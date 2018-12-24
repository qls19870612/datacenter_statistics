<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/15/015
 * Time: 18:19
 */

namespace task\realtime\online;


class PlatInfo
{
    private $platId;
    private $platName;
    private $serverInfoArr = array();

    /**
     * @return array
     */
    public function getServerInfoArr()
    {
        return $this->serverInfoArr;
    }

    /**
     * PlatInfo constructor.
     */
    public function __construct($platId, $platName)
    {
        $this->platId = $platId;
        $this->platName = $platName;
    }

    public function push(\stdClass $serverInfo)
    {
        $this->serverInfoArr[count($this->serverInfoArr)] = $serverInfo;
    }

    /**
     * @return mixed
     */
    public function getPlatId()
    {
        return $this->platId;
    }

    /**
     * @return mixed
     */
    public function getPlatName()
    {
        return $this->platName;
    }
}