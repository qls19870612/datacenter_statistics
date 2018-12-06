<?php
namespace task\tlbb;

use common;
use common\Helper;

/**
 *  汇总 t_player数据 ,统计程序
 * Class TPlayer
 */
class TPlayer extends common\HdfsTask {
    public function run() {
        $merge_date = date("Y-m-d", strtotime("-1 day", strtotime($this->dataDate)));

        $sql = "
select  iworldid,arbitrary(accountid),arbitrary(accountname),playerid,arbitrary(playername),arbitrary(job),max(level),min(createtime),max(lastlogintime)
from (
    select  iworldid , accountid  , accountname ,  playerid , playername , job , level , createtime , lastlogintime
    from  {$this->hiveSchema}.t_player
    where date='{$merge_date}' and plat ='{$this->platform}'

    union all
    select  iWorldId,iUin,iUin,iRoleId,vRoleName,iJobId, 1 as iRoleLevel,dtEventTime as dtCreateTime,dtEventTime
    from  {$this->hiveSchema}.CreateRole
    where date='{$this->dataDate}' and plat ='{$this->platform}'

    union all
    select  iWorldId,iUin,iUin,iRoleId,vRoleName,iJobId,iRoleLevel,dtCreateTime,dtEventTime
    from  {$this->hiveSchema}.RoleLogin
    where date='{$this->dataDate}' and plat ='{$this->platform}'

    union all
    select  iWorldId,iUin,iUin,iRoleId,vRoleName,iJobId,iRoleLevel,dtCreateTime,dtEventTime
    from  {$this->hiveSchema}.RoleLogout
    where date='{$this->dataDate}' and plat ='{$this->platform}'

    union all
    select  iWorldId,iUin,iUin,iRoleId,vRoleName,iJobId,iRoleLevel,dtCreateTime,dtEventTime
    from  {$this->hiveSchema}.RoleLevelUp
    where date='{$this->dataDate}'  and plat ='{$this->platform}'

) t
group by   iworldid,playerid
;";

        $this->fetchAndUpdate('t_player', $sql);
    }
}


