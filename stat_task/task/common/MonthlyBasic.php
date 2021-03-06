<?php
namespace task\common;

use common;

/**
 * Class MonthlyBasic
 * @package task\common
 * 每月区服基本信息汇总
 * 依赖:account_info
 */
class MonthlyBasic extends common\BaseTask
{
    public function run()
    {
        $stat_day = $this->dataDate;
        //只在月末执行一次
        if( !$this->isLastDayOfMonth($stat_day) ){
            return ;
        }

        //当前月份
        $stat_month = substr($stat_day,0,strrpos($stat_day,'-'));
        common\Helper::log('$stat_month:'.$stat_month);
        $table_name = 'monthly_basic';
        //按区服,帐号分组,聚合每天的数据
        $accountGroupSql = "
        select uin,worldid,
        min(registertime) registertime,max(LastLoginTime) LastLoginTime,
        min(FirstPayTime) FirstPayTime,max(LastPayTime) LastPayTime,
        sum(DayPayAmount) DayPayAmount,
        min(FirstShopTime) FirstShopTime,max(LastShopTime) LastShopTime,
        sum(DayShopAmount) DayShopAmount
        from account_info
        where dtstatdate between '{$stat_month}-01' and '{$stat_month}-31' 
        group by worldid,uin
        ";

        $sql = "
        select '{$stat_month}-01' dtstatdate,worldid,
        sum(if(substr(registertime,1,7)='{$stat_month}',1,0)) RegisterAccount,
        sum(if(substr(LastLoginTime,1,7)>='{$stat_month}',1,0)) LoginAccount,
        sum(if(DayPayAmount>0,1,0)) PayAccount,
        coalesce(sum(DayPayAmount),0) PayAmount,
        sum(if(substr(registertime,1,7)='{$stat_month}' and DayPayAmount>0,1,0)) NewPayAccount,
        coalesce(sum(if(substr(registertime,1,7)='{$stat_month}',DayPayAmount,0)),0) NewPayAmount,
        sum(if(DayShopAmount>0,1,0)) ShopAccount,
        coalesce(sum(DayShopAmount),0) ShopAmount
        from ( {$accountGroupSql} ) t
        group by worldid
        ";

        $this->fetchAndUpdate($table_name, $sql);
    }
}