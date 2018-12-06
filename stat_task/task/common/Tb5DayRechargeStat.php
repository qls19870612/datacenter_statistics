<?php
namespace task\common;

use common;
use common\Helper;

/**
 * 5天充值汇总
 * @package task\common
 */
class Tb5DayRechargeStat extends common\BaseTask {

    public function run() {
        $date_5_days_before = date('Y-m-d', strtotime('-5day', strtotime($this->dataDate)));
        $table_name = 'tb5dayrechargestat';
        $sql = "
SELECT
	tb.dtStatDate AS dtStatDate,
	tb.iWorldId AS iWorldid,
	COALESCE (ta.iRecharge, 0) AS iRecentRecharge,
	COALESCE (ta.iRoleNum, 0) AS iRecentRoleNum,
	(
		tb.iAllRecharge - COALESCE (ta.iRecharge, 0)
	) AS iOldRecharge,
	(
		tb.iAllRoleNum - COALESCE (ta.iRoleNum, 0)
	) AS iOldRoleNum,
	tb.iAllRecharge AS iAllRecharge,
	tb.iAllRoleNum AS iAllRoleNum
FROM
	(
		SELECT
			'{$this->dataDate}' AS dtStatDate,
			iWorldId,
			sum(iPayDelta) AS iAllRecharge,
			count(DISTINCT iRoleId) AS iAllRoleNum
		FROM
			{$this->hiveSchema}.Recharge
		WHERE
			date BETWEEN '{$this->dataDate}'
		AND '{$this->dataDateAfter}'
		AND plat = '{$this->platform}'
		AND dtEventTime BETWEEN '{$this->dataDate} 00:00:00'
		AND '{$this->dataDate} 23:59:59'
		GROUP BY
			iWorldId
	) tb -- 当天的充值汇总
LEFT JOIN (
	SELECT
		'{$this->dataDate}' AS dtStatDate,
		b.iWorldId AS iWorldId,
		sum(a.iPayDelta) AS iRecharge,
		count(DISTINCT iRoleId) AS iRoleNum
	FROM
		(
			SELECT
				iWorldId,
				PlayerID
			FROM
				{$this->hiveSchema}.t_player
			WHERE
				date = '{$this->dataDate}'
			AND plat = '{$this->platform}'
			AND CreateTime > '{$date_5_days_before} 23:59:59'
			AND CreateTime <= '{$this->dataDate} 23:59:59'
		) b -- 前五天的消费汇总
	LEFT JOIN (
		SELECT
			iWorldId,
			iRoleId,
			iPayDelta
		FROM
			{$this->hiveSchema}.Recharge
		WHERE
			date BETWEEN '{$this->dataDate}'
		AND '{$this->dataDateAfter}'
		AND plat = '{$this->platform}'
		AND dtEventTime BETWEEN '{$this->dataDate} 00:00:00'
		AND '{$this->dataDate} 23:59:59'
	) a -- 当天的消费汇总
	ON a.iWorldId = b.iWorldId
	AND a.iRoleId = b.PlayerID
	WHERE
		b.PlayerID IS NOT NULL
	GROUP BY
		b.iWorldId -- 统计日前5天包括统计日当天创角,在统计当天的按区服充值汇总
) ta ON ta.dtStatDate = tb.dtStatDate
AND ta.iWorldId = tb.iWorldId
";

        $this->fetchAndUpdate($table_name, $sql);
    }
}