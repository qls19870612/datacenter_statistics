<?php
namespace task\common;

use common;
use common\Helper;

/**
 * Class JobDistribution
 */
class TbPlayerJobStat extends common\BaseTask {

    public function run() {
        $table_name = 'tbplayerjobstat';
        $sql = "
select iWorldId ,Job as iJob,count(*) as  iCount
from {$this->hiveSchema}.t_player
where CreateTime <='{$this->dataDate} 23:59:59'
and date = '{$this->dataDate}'
and plat = '{$this->platform}'
group by  iWorldId ,Job
";

        $this->fetchAndUpdate($table_name, $sql);
    }
}