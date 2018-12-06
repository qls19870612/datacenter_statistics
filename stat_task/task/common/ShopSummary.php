<?php
namespace task\common;

use common;

/**
 * Class ShopSummary
 * @package task\common
 * 充值货币消费构成
 * 依赖:shop
 */
class ShopSummary extends common\BaseTask
{
    public function run()
    {
        $stat_day = $this->dataDate;
        $table_name = 'shop_summary';
        $sql = "
        select '{$stat_day}' dtstatdate,iworldid worldid,
        iShopType shoptype,iGoodsType goodstype,iGoodsId goodsid,
        sum(iGoodsNum) GoodsNum,sum(iCost) Amount,
        count(distinct iRoleId) RoleCount,count(1) BuyTimes
        from shop
        where dt='{$stat_day}' 
        group by iworldid,iShopType,iGoodsType,iGoodsId
        ";

        $this->fetchAndUpdate($table_name, $sql);
    }
}