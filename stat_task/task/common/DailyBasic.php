<?php
namespace task\common;

use common;

/**
 * 区服基本信息汇总
 * 依赖:account_info
 * @package task\common
 */
class DailyBasic extends common\BaseTask
{
    public function run()
    {
        $stat_day = $this->dataDate;
        $table_name = 'daily_basic';
        $sql = "
        select '{$stat_day}' dtStatDate,worldid,
        sum(if(substr(registertime,1,10)=dtstatdate,1,0)) RegisterAccount,
        sum(if(substr(LastLoginTime,1,10)=dtstatdate,1,0)) LoginAccount,
        sum(if(DayPayAmount>0,1,0)) PayAccount,
        coalesce(sum(DayPayAmount),0) PayAmount,
        sum(if(DayShopAmount>0,1,0)) ShopAccount,
        coalesce(sum(DayShopAmount),0) ShopAmount
        from account_info
        where dtstatdate='{$stat_day}' 
        group by worldid
        ";

        $this->fetchAndUpdate($table_name, $sql);
    }
}