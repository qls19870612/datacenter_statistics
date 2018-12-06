<?php
namespace task\common;

use common;
use common\Helper;


/**
 * Class JobDistribution
 */
class TbPlayerLevelStat extends common\BaseTask {

    public function run() {
        $table_name = 'tbplayerlevelstat';
        $sql = "
select  iWorldId ,Level as iLevel,count(*) as  iCount
from {$this->hiveSchema}.t_player
where CreateTime <='{$this->dataDate} 23:59:59'
and date = '{$this->dataDate}'
and plat = '{$this->platform}'
group by  iWorldId ,Level ";

        $this->fetchAndUpdate($table_name, $sql);
    }
}