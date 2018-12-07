<?php
namespace app;

use common\Helper;
use common\MysqlPdo;
use Grpc\Timeval;
use task;

/**
 * Class StatisticJob
 * @package app
 * 命令行参数： php main.php gameCode taskType/taskName startDate endDate platform
 * 第二个参数若含有'/',如common/Test表示是taskName;否则如daily表示是taskTye,则会执行config/tasklist.php中对应的任务列表,
 * taskType仅仅只是tasklist中的分类标记.
 * 省略startDate 和 endDate 则取前一天日期
 * 省略endDate则使endDate与startDate相同
 * 省略platform则表示所有平台
 * 只能省略最后的参数,不能省略中间的参数.
 */
class StatisticJob {

    public $taskName;

    public $dbConfig;

    /**
     * @var string 游戏名称 如 bhs
     */
    public $gameName;

    /**
     * @var string 游戏代号 如 bhs_cn
     */
    public $gameCode;

    public $platform;

    /**
     * @var string 数据起始日期
     */
    public $startDate = null;

    /**
     * @var string 数据结束日期
     */
    public $endDate = null;

    /**
     * @var int 脚本运行开始时间
     */
    public $startTime;

    public $dataDate;

    public $taskConfig;

    public $taskType;

    public function __construct($config) {
        $this->readInputParams();
        $this->setDbConfig($config['db']);
        $this->setTaskConfig($config['task']);
    }

    /**
     * 读取命令行输入参数
     */
    protected function readInputParams() {
        $this->setGameCode();
        $this->setTaskType();
        $this->setDate();
    }

    public function run() {
        $runTime = date('Y-m-d H:i:s');//脚本开始运行时间,DIP采集基准时间
        $this->beginLog();
        $this->readPlatformData();

        $curDate = $this->startDate;
        $resultData = array();

        while (strtotime($curDate) <= strtotime($this->endDate)) {//遍历日期
            Helper::log('============================================');
            Helper::log("[DATE START] $curDate");
            $dayStartTime = microtime(true);
            foreach ($this->taskConfig as $taskName) {
                Helper::log('---------------------------------------');//遍历任务
                Helper::log("[TASK START] $taskName");
                $taskStartTime = microtime(true);
                $className = 'task\\' . str_replace('/', '\\', $taskName);
                $count = 1;//平台计数
                foreach ($this->platform as $plt) {//遍历平台
                    $pltName = $plt['vPname'];
                    $pltId = $plt['iPid'];
                    Helper::log("#{$count} name: {$pltName} platform id: {$pltId}");
                    $task = new $className($taskName, $this->gameCode, $this->gameName, $pltName, $pltId, $this->dbConfig, $curDate, $runTime);
                    /* @var $task \common\BaseTask */
                    $task->init();
                    $resultData[$pltName] = $task->run();
                    $count++;
                }
                Helper::log("[TASK END] $taskName , time elapsed: " . Helper::formatTimeInterval(microtime(true) - $taskStartTime));
            }
            Helper::log("[DATE END] $curDate , time elapsed: " . Helper::formatTimeInterval(microtime(true) - $dayStartTime));
            $curDate = date('Y-m-d', strtotime("+1 day", strtotime($curDate)));
        }

        $this->endLog();
    }

    protected function beginLog() {
        $this->startTime = microtime(true);
        Helper::log('###############################################');
        Helper::log("[START] game:$this->gameCode, task_type:$this->taskType, task_count:" . count($this->taskConfig) . " ,debug:" . (DEBUG ? 'TRUE' : 'FALSE'));
        Helper::log("FROM {$this->startDate} TO {$this->endDate}");
    }

    protected function endLog() {
        Helper::log("[END] time elapsed:" . Helper::formatTimeInterval(microtime(true) - $this->startTime));
        Helper::moveTmpLog();//只在成功完成任务时,将临时日志转移至主日志
    }

    protected function readPlatformData() {
        if (isset($_SERVER['argv'][5])) {
            $dbConf = new MysqlPdo($this->dbConfig['result']);
            $this->platform = $dbConf->fetchAll("
                  select * from db{$this->gameName}conf.tbplt
                  where vPname='{$_SERVER['argv'][5]}'
                  order by iPid limit 1
            ");
        } elseif (isset($this->dbConfig['platform'])) {
            $this->platform = $this->dbConfig['platform'];
        } else {
            $dbConf = new MysqlPdo($this->dbConfig['result']);
            $this->platform = $dbConf->fetchAll("select * from db{$this->gameName}conf.tbplt order by vPname");
        }

        Helper::log('read ' . count($this->platform) . ' platforms.');
        if (empty($this->platform)) {
            exit('no platform data.');
        }
    }

    protected function setTaskType() {
        if (isset($_SERVER['argv']) && isset($_SERVER['argv'][2])) {
            $task = $_SERVER['argv'][2];
            if (stripos($task, '/') === false) {
                $this->taskType = $task;
            } else {
                $this->taskName = $task;
            }
        } else {
            exit('no task name');
        }
    }

    protected function setGameCode() {
        if (isset($_SERVER['argv']) && isset($_SERVER['argv'][1])) {
            $this->gameCode = $_SERVER['argv'][1];
            Helper::$gameCode = $this->gameCode;
        } else {
            exit('no game code');
        }
    }

    protected function setDbConfig($config) {
        if (isset($config[$this->gameCode])) {
            $this->dbConfig = $config[$this->gameCode];
            $this->gameName = $this->dbConfig['name'];
        } else {
            exit('can not find db config.');
        }

    }

    protected function setDate() {
        //开始日期处理
        $afterDay = 365;//最大可统计往后一年
        if (isset($_SERVER['argv'][3])) {
            $inputStartDate = $_SERVER['argv'][3];
            if($inputStartDate <=$afterDay)
            {
                $this->startDate = date('Y-m-d', time() + $inputStartDate * 86400);
            }
            else{
                if (strtotime($inputStartDate) !== false) {
                    $this->startDate = date('Y-m-d', strtotime($inputStartDate));
                } else {
                    Helper::log('wrong startDate param');
                    exit(1);
                }
            }

        } else {
            $this->startDate = date('Y-m-d', strtotime('-1 day'));
        }

        //结束日期
        if (isset($_SERVER['argv'][4])) {
            $inputEndDate = $_SERVER['argv'][4];
            if($inputEndDate <=$afterDay)
            {
                $this->endDate = date('Y-m-d', time() + $inputEndDate * 86400);
            }
            else{
                if (strtotime($inputEndDate) !== false) {
                    $this->endDate = date('Y-m-d', strtotime($inputEndDate));
                } else {
                    Helper::log('wrong endDate param');
                    exit(1);
                }
            }

        } else {
            $this->endDate = $this->startDate;
        }
        if(strtotime($this->startDate) > strtotime($this->endDate))
        {
            Helper::log("start day can not bigger than end date!");
            exit(1);
        }

    }

    protected function setTaskConfig($config) {
        if (!empty($this->taskName)) {
            $this->taskConfig = array($this->taskName);
        } elseif (!empty($this->taskType) && isset($config[$this->gameCode][$this->taskType])) {
            $this->taskConfig = $config[$this->gameCode][$this->taskType];
        } else {
            exit('can not find task config.');
        }

    }


}