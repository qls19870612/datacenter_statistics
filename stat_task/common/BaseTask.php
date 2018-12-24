<?php
namespace common;

class BaseTask {

    /**
     * @var string 任务名称，包括文件夹，如once/Test
     */
    public $taskName;

    /**
     * @var string 平台
     */
    public $platform;

    /**
     * @var string 平台ID
     */
    public $platformId;

    /**
     * @var PrestoClient
     */
    public $dbLog;

    /**
     * @var MysqlPdo
     */
    public $dbResult;

    public $dbConfig;

    public $gameCode;

    public $gameName;

    /**
     * @var hive库名
     */
    public $hiveSchema;

    /**
     * @var string 存放输出文件的路径
     */
    public $outputPath;

    /**
     * @var string 指定数据的日期
     */
    public $dataDate;

    /**
     * @var string 指定数据的日期 的后一天
     */
    public $dataDateAfter;

    public $runTime;

    const DB_TYPE_LOG = 'log';
    const DB_TYPE_RESULT = 'result';

    public function __construct($taskName, $gameCode, $gameName, $platform, $platformId, $dbConfig, $dataDate, $runTime) {
        $this->taskName = $taskName;
        $this->platform = $platform;
        $this->dbConfig = $dbConfig;
        $this->gameCode = $gameCode;
        $this->gameName = $gameName;
        $this->dataDate = $dataDate;
        $this->dataDateAfter = date('Y-m-d', strtotime('+1 day', strtotime($dataDate)));
        $this->platformId = $platformId;
        $this->runTime = $runTime;
    }

    /**
     * 初始化工作可以由子类实现
     */
    public function init() {
//        $this->hiveSchema = isset($this->dbConfig['hive_schema']) ? $this->dbConfig['hive_schema'] : $this->gameName;
//        $this->dbLog = new PrestoClient($this->hiveSchema);
//        if (isset($this->dbConfig['result'])) {
//            $this->dbResult = new MysqlPdo($this->dbConfig['result']);
//            $this->dbResult->query("USE `db{$this->gameName}{$this->platform}result`");
//        }
        $this->hiveSchema ="db{$this->gameName}{$this->platform}log";
        $this->dbLog = new MysqlPdo($this->dbConfig['log']);
        $this->dbLog->query("USE `{$this->hiveSchema}`");
        if (isset($this->dbConfig['result'])) {
            $this->dbResult = new MysqlPdo($this->dbConfig['result']);
            $this->dbResult->query("USE `db{$this->gameName}{$this->platform}result`");
        }

    }

    /**
     * 需要由子类实现
     */
    public function run() {
        return true;
    }

    /**
     * 设置输出文件的路径，并创建输出文件夹
     * 用作临时文件存放地址
     */
    public function initOutputFolder() {
        $this->outputPath = dirname(__DIR__) . '/data/' . $this->gameCode . '/' . str_replace('/', '_', $this->taskName) . '/' . date('Ymd') . uniqid('_') . '/';
        if (!is_dir($this->outputPath)) {
            mkdir($this->outputPath, 0777, true);
        }
    }

    /**
     * 用$sql获取的数据更新$table
     * 注意:修改的时候注意子类要对应修改.
     * @param $table
     * @param $sql
     * @param $dtStatDate string 统计结果日期
     * @param $fetch_from string 数据源
     * @param bool $deleteOldData 更新前删除旧数据
     * @return number|bool 成功返回读取的数据行数,失败返回false
     */
    public function fetchAndUpdate($table, $sql, $dtStatDate = null, $fetch_from = self::DB_TYPE_RESULT, $deleteOldData = true) {
        $fetched_data_arr = $this->fetch($sql, $fetch_from);
        if ($fetched_data_arr === false) {
            return false;
        } else {
            return $this->update($table, $fetched_data_arr, $dtStatDate, $deleteOldData);
        }
    }

    /**
     * 更新结果库
     * @param $table string 更新的表名
     * @param $data array 用来更新的数据
     * @param null $dtStatDate 指定更新的日期
     * @param bool $deleteOldData 更新前删除旧数据
     * @return bool|int 返回更新行数
     */
    public function update($table, $data, $dtStatDate = null, $deleteOldData = true) {
//        Helper::log("updating result db `$table` .....");
        if (empty($data)) {
            return 0;
        }
        if ($dtStatDate == null) {
            $dtStatDate = $this->dataDate;
        }
        $this->dbResult->beginTransaction();
        if ($deleteOldData) {
            $this->dbResult->query("delete from $table  where dtStatDate ='{$dtStatDate}' ");
        }
        foreach ($data as $row) {
            if (!isset($row['dtStatDate']) && !isset($row['dtstatdate'])) {
                $row['dtStatDate'] = $dtStatDate;
            }
            $this->dbResult->insert($table, $row);
        }

        if ($this->dbResult->ERROR) {
            Helper::log('错误:' . $this->dbResult->ERROR[0]);
            $this->dbResult->rollback();
            $this->dbResult->ERROR = array();
            return false;
        } else {
            $this->dbResult->commit();
            return count($data);
        }
    }

    /**
     * 查询数据
     * @param $sql
     * @param string $fetch_from 查询的数据库类型,LOG or RESULT(mysql)
     * @return array|bool 返回查询到的数据
     */
    public function fetch($sql, $fetch_from = self::DB_TYPE_LOG) {
        Helper::log('fetching data.....');
        if ($fetch_from == self::DB_TYPE_RESULT) {
            $fetched_data_arr = $this->dbResult->fetchAll($sql);
        } else {
            $fetched_data_arr = $this->dbLog->fetchAll($sql);
            if ($fetched_data_arr === false) {
                Helper::log('fetching data FAILED !');
                Helper::log($this->dbLog->getDbError());
                return false;
            }
        }
        return $fetched_data_arr;
    }

    /**
     * @param string $date
     * @return bool
     * 判断参数日期字符串是否当前月最后一天
     */
    public function isLastDayOfMonth($date)
    {
        $date = substr($date,0,10);
        //下个月
        $nextMonth = date('Y-m', strtotime('+1 month', strtotime($date)));
        //本月最后一天
        $lastDay = date('Y-m-d', strtotime('-1 day', strtotime($nextMonth . '-01')));
        if($date === $lastDay){
            return true;
        }else{
            return false;
        }
    }
}