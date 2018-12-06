<?php
namespace task\tlbb;

use common;

class TbGoodsFlowConsumeStatNew extends common\BaseTask {

    public function run() {
        $table_name = 'tbgoodsflowconsumestat_new';
        $sql = "
SELECT   iWorldId, vOperate, iGoodsId,abs(sum(iCount)) AS iCount ,count(DISTINCT iRoleId) AS iUserNum,count(iCount) AS iNum
FROM    GoodsFlow
WHERE   iCount < 0
AND date BETWEEN '{$this->dataDate}' and '{$this->dataDateAfter}'
AND plat = '{$this->platform}'
and dteventtime BETWEEN '{$this->dataDate} 00:00:00' and '{$this->dataDate} 23:59:59'
GROUP BY  iWorldId, vOperate, iGoodsId
";

        $this->fetchAndUpdate($table_name, $sql);
    }
}