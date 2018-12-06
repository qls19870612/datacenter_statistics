<?php
namespace task\common;

use common;

/**
 * Class DailyLTV
 * @package task\common
 * 每日LTV
 * 依赖:account_info
 */
class DailyLTV extends common\BaseTask
{
    public function run()
    {
        $stat_day = $this->dataDate;
        $table_name = 'daily_ltv';
        $sql = "
        select dtstatdate,worldid,
        substr(RegisterTime,1,10) RegisterDate,count(*) RegisterNum,
        coalesce(sum(TotalPayAmount),0) TotalPayAmount
        from account_info
        where dtstatdate='{$stat_day}'
        and RegisterTime is not null
        group by dtstatdate,worldid,substr(RegisterTime,1,10)
        ";

        $this->fetchAndUpdate($table_name, $sql);
    }
}