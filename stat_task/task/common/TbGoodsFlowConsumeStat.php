<?php
namespace task\common;

use common;

class TbGoodsFlowConsumeStat extends common\BaseTask {

    public function run() {
        $table_name = 'tbgoodsflowconsumestat';
        $sql = "
SELECT   iWorldId, vOperate, iGoodsId,sum(iCount) AS iCount ,count(DISTINCT iRoleId) AS iUserNum,count(iCount) AS iNum
FROM    {$this->hiveSchema}.GoodsFlow
WHERE   iIdentifier=2
and dteventtime BETWEEN '{$this->dataDate} 00:00:00' and '{$this->dataDate} 23:59:59'
GROUP BY  iWorldId, vOperate, iGoodsId
";

        $this->fetchAndUpdate($table_name, $sql,null,self::DB_TYPE_LOG);
    }
}