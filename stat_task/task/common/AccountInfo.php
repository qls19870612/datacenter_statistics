<?php
namespace task\common;

use common;

/**
 * 帐号信息汇总(分区服)
 * 依赖:上一天的统计结果
 */
class AccountInfo extends common\BaseTask
{
    public function run()
    {
        $stat_day = $this->dataDate;
        $merge_date = date("Y-m-d", strtotime("-1 day", strtotime($this->dataDate)));
        $table_name="account_info";

        $rolelogin = "
        select iuin Uin,iworldid WorldId,
        dtEventTime RegisterTime,dtEventTime LastLoginTime,
        null FirstPayTime,null LastPayTime,
        null DayPayAmount,null DayPayTimes,
        null TotalPayAmount,null TotalPayTimes,
        null FirstShopTime,null LastShopTime,
        null DayShopAmount,null DayShopTimes,
        null TotalShopAmount,null TotalShopTimes
        from rolelogin
        where dt='{$stat_day}'
        ";

        $createrole = "
        select iuin Uin,iworldid WorldId,
        dtEventTime RegisterTime,dtEventTime LastLoginTime,
        null FirstPayTime,null LastPayTime,
        null DayPayAmount,null DayPayTimes,
        null TotalPayAmount,null TotalPayTimes,
        null FirstShopTime,null LastShopTime,
        null DayShopAmount,null DayShopTimes,
        null TotalShopAmount,null TotalShopTimes
        from createrole
        where dt='{$stat_day}'
        ";

        $recharge = "
        select iuin Uin,iworldid WorldId,
        null RegisterTime,null LastLoginTime,
        dtEventTime FirstPayTime,dtEventTime LastPayTime,
        ipaydelta DayPayAmount,1 DayPayTimes,
        ipaydelta TotalPayAmount,1 TotalPayTimes,
        null FirstShopTime,null LastShopTime,
        null DayShopAmount,null DayShopTimes,
        null TotalShopAmount,null TotalShopTimes
        from recharge
        where dt='{$stat_day}'
        ";

        $shop = "
        select iuin Uin,iworldid WorldId,
        null RegisterTime,null LastLoginTime,
        null FirstPayTime,null LastPayTime,
        null DayPayAmount,null DayPayTimes,
        null TotalPayAmount,null TotalPayTimes,
        dtEventTime FirstShopTime,dtEventTime LastShopTime,
        iCost DayShopAmount,1 DayShopTimes,
        iCost TotalShopAmount,1 TotalShopTimes
        from shop
        where dt='{$stat_day}'
        ";

        $yesterday = "
        select Uin,WorldId,
        RegisterTime,LastLoginTime,
        FirstPayTime,LastPayTime,
        0 DayPayAmount,0 DayPayTimes,
        TotalPayAmount,TotalPayTimes,
        FirstShopTime,LastShopTime,
        0 DayShopAmount,0 DayShopTimes,
        TotalShopAmount,TotalShopTimes
        from db{$this->gameName}{$this->platform}result.account_info
        where dtStatDate='{$merge_date}'  
        ";

        $sql = "
        select uin,worldid,
        min(RegisterTime) registertime,max(LastLoginTime) lastlogintime,
        min(FirstPayTime) firstpaytime,max(LastPayTime) lastpaytime,
        sum(DayPayAmount) daypayamount,sum(DayPayTimes) daypaytimes,
        sum(TotalPayAmount) totalpayamount,sum(TotalPayTimes) totalpaytimes,
        min(FirstShopTime) firstshoptime,max(LastShopTime) lastshoptime,
        sum(DayShopAmount) dayshopamount,sum(DayShopTimes) dayshoptimes,
        sum(TotalShopAmount) totalshopamount,sum(TotalShopTimes) totalshoptimes,
        '{$stat_day}' dtStatDate
        from ( {$yesterday}
        union all 
        {$rolelogin}
        union all 
        {$createrole}
        union all 
        {$recharge}
        union all 
        {$shop}
        ) tpl 
        group by uin,worldid 
        ";

        $this->fetchAndUpdate($table_name, $sql, null, self::DB_TYPE_RESULT);

    }
}