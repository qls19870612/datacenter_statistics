<?php
namespace task\common;

use common;

class TbGoodsFlowProduceStat extends common\BaseTask {

    public function run() {
        $table_name = 'tbgoodsflowproducestat';
        $sql = "
SELECT   iWorldId, vOperate ,  iGoodsId,  sum(iCount) AS iCount ,count(DISTINCT iRoleId) AS iUserNum,count(iCount) AS iNum
FROM    {$this->hiveSchema}.GoodsFlow
WHERE   iCount>0
AND date BETWEEN '{$this->dataDate}' and '{$this->dataDateAfter}'
AND plat = '{$this->platform}'
and dteventtime BETWEEN '{$this->dataDate} 00:00:00' and '{$this->dataDate} 23:59:59'
GROUP BY  iWorldId, vOperate, iGoodsId
";

        $this->fetchAndUpdate($table_name, $sql);
    }
}