<?php
namespace task\tlbb;

use common;

/**
 * 统计商城购买
 * @package task\common
 */
class TbItemSalesStat extends common\BaseTask {

    public function run() {
        $table_name = 'tbitemsalesstat';
        $sql = "
(
	SELECT
		iWorldId,
		2 AS iMoneyType,
		iShopType,
		iGoodsType,
		iGoodsId,
		sum(iGoodsNum) AS iGoodsNum,
		sum(iCost) AS iPayment,
		sum(iCost) AS iPaymentGold,
		0 AS iPaymentPoint,
		0 AS iPaymentCoin,
		0 AS iPaymentDirect,
		count(DISTINCT iRoleId) AS iUserNum,
		count(*) AS iNum
	FROM
		Shop
	WHERE
		dtEventTime >= '{$this->dataDate} 00:00:00'
	AND dtEventTime <= '{$this->dataDate} 23:59:59'
	AND date = '{$this->dataDate}'
	AND plat = '{$this->platform}'
	GROUP BY
		iWorldId,
		iShopType,
		iGoodsType,
		iGoodsId
)
UNION
	(
		SELECT
			123456789 AS iWorldId,
			2 AS iMoneyType,
			iShopType,
			iGoodsType,
			iGoodsId,
			sum(iGoodsNum) AS iGoodsNum,
			sum(iCost) AS iPayment,
			sum(iCost) AS iPaymentGold,
			0 AS iPaymentPoint,
			0 AS iPaymentCoin,
			0 AS iPaymentDirect,
			count(DISTINCT iRoleId) AS iUserNum,
			count(*) AS iNum
		FROM
			Shop
		WHERE
			dtEventTime >= '{$this->dataDate} 00:00:00'
		AND dtEventTime <= '{$this->dataDate} 23:59:59'
		AND date = '{$this->dataDate}'
		AND plat = '{$this->platform}'
		GROUP BY
			iShopType,
			iGoodsType,
			iGoodsId
	)
        ";
        $this->fetchAndUpdate($table_name, $sql);
    }
}