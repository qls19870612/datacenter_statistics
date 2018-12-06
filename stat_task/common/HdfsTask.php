<?php
namespace common;


class HdfsTask extends BaseTask {

    /**
     * 用$sql获取的数据更新$table
     * @param $table
     * @param $sql
     * @param $dtStatDate string 统计结果日期
     * @param $fetch_from string 数据源
     * @return number|bool 成功返回读取的数据行数,失败返回false
     */
    public function fetchAndUpdate($table, $sql, $dtStatDate = null, $fetch_from = self::DB_TYPE_LOG, $deleteOldData = true) {
        $hdfs = '/data/hadoop/hadoop/hadoop-2.4.1/bin/hdfs';
        $presto = '/data/presto/presto/presto';
        $hadoop_path = "/user/hive/warehouse/{$this->hiveSchema}.db/$table/plat={$this->platform}/date={$this->dataDate}";
        $this->initOutputFolder();
        $tmp_conf = $this->outputPath . "query.conf";
        $tmp_data = $this->outputPath . "query.txt";

        file_put_contents($tmp_conf, $sql);

        Helper::log('querying');
        $shell = "$presto  --output-format TSV   --server hadoop-c1-r1-f1-s2:8080 --catalog hive --schema {$this->hiveSchema} -f $tmp_conf > $tmp_data; ";
        $this->exec_shell($shell);

        Helper::log('deleting old data');
        $shell = "$hdfs  dfs   -rm -skipTrash {$hadoop_path}/query.txt ";
        $this->exec_shell($shell);

        Helper::log('inserting');
        $shell = "$hdfs dfs -put {$tmp_data}  hdfs://mldc-hadoop{$hadoop_path}/";
        $this->exec_shell($shell);
    }

    public function exec_shell($shell) {
//        echo 'SHELL:' . $shell;
        $shell_result = exec($shell);
        if (!empty($shell_result)) {
            Helper::log('RESULT:' . $shell_result);
        }
    }

    /**
     * 从结果库获取数据更新到hive
     * @param $table
     * @param $sql
     */
    public function multiGetAndPut($table, $sql){
        $hdfs = '/data/hadoop/hadoop/hadoop-2.4.1/bin/hdfs';
        $presto = '/data/presto/presto/presto';
        $hadoop_path = "/user/hive/warehouse/{$this->hiveSchema}.db/$table/plat={$this->platform}";
        $this->initOutputFolder();
        $tmp_conf = $this->outputPath . "query.conf";
        $tmp_data = $this->outputPath . "query.txt";

        file_put_contents($tmp_conf, $sql);

        Helper::log('querying');
        $data_arr = array();
        $out = $this->dbResult->fetchAll($sql); //返回数据数组
        //echo '<pre>';print_r($out);die;
        foreach( $out as $val ){
            $data_arr[] = implode('|', $val);
        }
        $data_str = implode("\n", $data_arr);
        //echo $data_str;die;
        file_put_contents($tmp_data, $data_str);

        if ($this->dbResult->ERROR) {
            Helper::log('错误:' . $this->dbResult->ERROR[0]);
            $this->dbResult->ERROR = array();
        }

        Helper::log('deleting old data');
        $shell = "$hdfs  dfs   -rm -skipTrash {$hadoop_path}/* "; //删掉tbworldbegindate(目前是这个表)该分区下所有文本
        $this->exec_shell($shell);

        Helper::log('inserting');
        $shell = "$hdfs dfs -put {$tmp_data}  hdfs://mldc-hadoop{$hadoop_path}/";
        $this->exec_shell($shell);
    }

    /**
     * 用$sql获取的数据更新$table
     * @param $table
     * @param $sql
     * @param $suffix string 库名后缀添加
     * @param $dtStatDate string 统计结果日期
     * @param $fetch_from string 数据源
     * @return number|bool 成功返回读取的数据行数,失败返回false
     */
    public function fetchAndUpdateAdd($table, $sql, $suffix = '', $dtStatDate = null, $fetch_from = self::DB_TYPE_LOG, $deleteOldData = true) {
        $hdfs = '/data/hadoop/hadoop/hadoop-2.4.1/bin/hdfs';
        $presto = '/data/presto/presto/presto';
        $hadoop_path = "/user/hive/warehouse/{$this->hiveSchema}{$suffix}.db/$table/plat={$this->platform}/date={$this->dataDate}";
        $this->initOutputFolder();
        $tmp_conf = $this->outputPath . "query.conf";
        $tmp_data = $this->outputPath . "query.txt";

        file_put_contents($tmp_conf, $sql);

        Helper::log('querying');
        $shell = "$presto  --output-format TSV   --server hadoop-c1-r1-f1-s2:8080 --catalog hive --schema {$this->hiveSchema} -f $tmp_conf > $tmp_data; ";
        $this->exec_shell($shell);

        Helper::log('deleting old data');
        $shell = "$hdfs  dfs   -rm -skipTrash {$hadoop_path}/query.txt ";
        $this->exec_shell($shell);

        Helper::log('inserting');
        $shell = "$hdfs dfs -put {$tmp_data}  hdfs://mldc-hadoop{$hadoop_path}/";
        $this->exec_shell($shell);
    }

}