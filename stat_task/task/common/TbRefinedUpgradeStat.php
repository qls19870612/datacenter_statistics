<?php
namespace task\common;

use common;
use common\Helper;

/**
 * 特性连升统计
 * @package task\common
 */
class TbRefinedUpgradeStat extends common\BaseTask {

    public function run() {
        $stat_day = $this->dataDate;
        $table_name = 'tbrefinedupgradestat';
        $sql = "
SELECT
	iWorldId,
	iType,
	iLevel,
	iLevelsUp,
	count(*) AS iNum
FROM
	(
		SELECT
			iType,
			CASE
				WHEN iType NOT IN (8,9) THEN 0
				WHEN iAfterLevel BETWEEN 0 AND 29 THEN 1
				WHEN iAfterLevel BETWEEN 30 AND 59 THEN 4
				WHEN iAfterLevel >= 60 THEN 7
			END AS iLevel,
			iWorldId,
			COUNT(*) AS iLevelsUp
	FROM
		{$this->gameName}.RefinedUpgrade
	WHERE
		dtEventTime BETWEEN '{$stat_day} 00:00:00' AND '{$stat_day} 23:59:59'
		AND iType IN (1,2,3,4,8,9)
	GROUP BY
		iType,
		iLevel,
		iWorldId,
		iRoleId
	) AS a
GROUP BY
	iWorldId,
	iType,
	iLevel,
	iLevelsUp
;";

        $this->fetchAndUpdate($table_name, $sql);
    }
}