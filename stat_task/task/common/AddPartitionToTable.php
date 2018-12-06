<?php
namespace task\common;

use common;
use common\Helper;

/**
 * Class DailyAddPartitionToTable
 * 给log表添加表分区
 * 在当前分区上加一天的分区
 * @package task
 */
class AddPartitionToTable extends common\BaseTask {

    public function init() {
        //根据具体库名做改变
        $this->dbLog->query("USE db{$this->gameName}{$this->platform}log;");
    }

    public function run() {
        //根据具体日志表做改变
        $tableList = array('accountlogin', 'cashlog', 'createrole', 'doing_bow', 'doing_class', 'doing_item', 'doing_keepsake',
            'doing_level', 'doing_mount', 'doing_precious', 'doing_ridingweapon', 'doing_souls', 'onlinecount', 'recharge', 'rolelogin');

        foreach ($tableList as $tb) {//循环表名
            $tableName = $this->platform . '_' . $tb;
            if (!$this->checkTable($tableName)) {
                continue;
            }
            $maxPartition = $this->getMaxPartition($tableName);
            $nextTime=strtotime(str_replace('p_', '', $maxPartition))+3600*24;
            $p_value = $this->to_days(date('Y-m-d', $nextTime));
            $p_name = 'p_' . date('Ymd', $nextTime);
            $sql = "alter table $tableName add partition (partition $p_name values in ($p_value) engine=MyISAM);";
            common\Helper::log($sql);
            $this->dbLog->query($sql);
        }
    }

    protected function checkTable($tableName) {
        return (bool)$this->dbLog->fetchRow("show tables like '$tableName'");
    }

    protected function getMaxPartition($tableName) {
        $row = $this->dbLog->fetchRow("
            SELECT MAX(PARTITION_NAME) p_name
            FROM information_schema.PARTITIONS
            WHERE table_schema = SCHEMA()
            AND table_name = '{$tableName}'
        ");
        return $row['p_name'];
    }

    protected function to_days($date) {
        $bits = explode('-', $date, 2);
        $year = $bits[0];
        if ($this->is_leap_year($year)) {
            $bits[0] = '2000';
        } else {
            $bits[0] = '1999';
        }
        $date = implode('-', $bits);
        $leaps = 387;    //leap years up to 1600
        for ($i = 1600; $i < $year; $i++) {
            if ($this->is_leap_year($i)) {
                ++$leaps;
            }
        }
        $days = date('z', strtotime($date));
        return $leaps + ($year * 365) + $days + 1;
    }

    protected function is_leap_year($year) {
        if ($year % 100 == 0 && $year % 400 == 0) {
            return true;
        }
        if ($year % 100 == 0) {
            return false;
        }
        if ($year % 4 == 0) {
            return true;
        }
        return false;
    }
}

