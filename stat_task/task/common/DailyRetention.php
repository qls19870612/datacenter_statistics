<?php
namespace task\common;

use common;

/**
 * Class DailyRetention
 * @package task\common
 * 每日留存率
 */
class DailyRetention extends common\BaseTask
{
    public function run()
    {
        $stat_day = $this->dataDate;
        $table_name = 'daily_retention';
        $db_name = "db{$this->gameName}{$this->platform}result";
        $sql = "
        select dtstatdate,worldid,
        substr(RegisterTime,1,10) RegisterDate,count(*) RetentionNum
        from {$db_name}.account_info
        where dtstatdate='{$stat_day}' and substr(LastLoginTime,1,10)='{$stat_day}'
        and RegisterTime is not null
        group by dtstatdate,worldid,substr(RegisterTime,1,10)
        ";

        $this->fetchAndUpdate($table_name, $sql);
    }
}