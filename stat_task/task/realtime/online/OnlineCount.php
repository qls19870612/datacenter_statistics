<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/14/014
 * Time: 20:52
 */

namespace task\realtime\online;

use common;
use common\Helper;

class OnlineCount extends common\BaseTask
{

    private $serverId;

    public function setInfo($serverId)
    {
        $this->serverId = $serverId;
    }


    protected function readInputParams()
    {
        $this->setGameCode();
    }

    protected function setGameCode()
    {
        if (isset($_SERVER['argv']) && isset($_SERVER['argv'][1])) {
            $this->gameCode = $_SERVER['argv'][1];
            Helper::$gameCode = $this->gameCode;
        } else {
            exit('no game code');
        }
    }
}