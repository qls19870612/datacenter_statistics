<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/18/018
 * Time: 17:27
 */

namespace task\realtime\recharge;

use common;
use common\Helper;

class RechargeCount extends common\BaseTask
{

    private
        $serverId;

    public
    function setInfo($serverId)
    {
        $this->serverId = $serverId;
    }


    protected
    function readInputParams()
    {
        $this->setGameCode();
    }

    protected
    function setGameCode()
    {
        if (isset($_SERVER['argv']) && isset($_SERVER['argv'][1])) {
            $this->gameCode = $_SERVER['argv'][1];
            Helper::$gameCode = $this->gameCode;
        } else {
            exit('no game code');
        }
    }
}