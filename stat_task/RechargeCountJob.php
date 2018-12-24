<?php
/**
 * Created by PhpStorm.
 * User: liangsong
 * Date: 2018/12/15/015
 * Time: 14:11
 */

namespace app;


use common\Helper;
use common\ParallelCurl\AsyncOperation;
use task\realtime\RealCountConfig;
use task\realtime\recharge\RechargeCount;


include 'httputils\curl.class.php';

class RechargeCountJob extends StatisticJob
{
    private $serverList = null;
    private $callCount = 0;
    /** @var array \task\realtime\RechargeCount */
    private $platTasks;


    protected function readInputParams()
    {
        $this->setGameCode();

    }

    protected function setTaskConfig($config)
    {

    }

    protected function initConfig($config)
    {
        parent::initConfig($config); //
        RealCountConfig::initRechargeUrl($config['servers_url'], $config['recharge_url'], $config['server_key']);
    }

    public function run()
    {

        $this->readServerList();

        $runTime = date('Y-m-d H:i:s');//脚本开始运行时间,DIP采集基准时间
        $this->beginLog();
        $this->readPlatformData();

        $curDate = $this->startDate;
        $taskStartTime = microtime(true);
        $taskName = 'RechargeCount';

        $this->requestCurl($taskName, $curDate, $runTime);
        Helper::log("[TASK END] $taskName , time elapsed: " . Helper::formatTimeInterval(microtime(true) - $taskStartTime));

        $this->endLog();
    }

    private function readServerList()
    {
        $serverListPath = dirname(__DIR__) . '/serverlist.cache';

        if (file_exists($serverListPath)) {
            $fileModifyTime = filemtime($serverListPath);
            Helper::log('$fileModifyTime:' . $fileModifyTime);
            if ($fileModifyTime + 10 * 60000 > time()) {
                //文件未过期
                $serverListData = file_get_contents($serverListPath);
                $this->serverList = json_decode($serverListData);
            }
        }
        if ($this->serverList == null) {

            $sid = 1;
            $httpClient = new AsyncOperation(RealCountConfig::$rechargeUrl, array(), $sid);
            $httpClient->start() && $httpClient->join();
            $returnData = $httpClient->storage[$sid];
            file_put_contents($serverListPath, $returnData);
            $this->serverList = json_decode($returnData);
        }
    }

    function callback($response, $info, $error, $request)
    {
        $this->callCount++;

        $decodedJson = json_decode($response);
        $result = $decodedJson->Result;
        if ($result != 0) {
            Helper::log("count recharge fail errorCode:$result");
            return;
        }
        $matches = array();

        parse_str(substr($info['url'], strrpos($info['url'], '?') + 1), $matches);
        $operator = $matches['operator'];
        $endTime = $matches['EndTime'];
        /** @var RechargeCount $task */
        $task = $this->platTasks[$operator];
        $dtStatDate = $task->runTime;
        $worldList = [array(
            'iHourRecharge' => $decodedJson->total,
            'dtStatTime' => date('Y-m-d H:i:s', $endTime),
            'iWorldId' => $matches['WorldID'])];
        if (count($worldList) > 0) {
            $task->update('tbrealrecharge', $worldList, $dtStatDate, false);
        }
    }

    private function requestCurl($taskName, $curDate, $runTime)
    {

        $curl = new Curl ($this, "callback");
        $count = 1;//平台计数
//        for ($i = 0; $i < 1000; $i++) {
        foreach ($this->serverList as $server) {//遍历平台

            $data = array();
            $data['StartTime'] = strtotime(date('Y-m-d'));// + date('H') * 3600;
            $data['EndTime'] = $data['time'] = time();

            $data['operator'] = $server->pid;
            if (!($task = $this->platTasks[$server->pid])) {
                $task = new RechargeCount($taskName . $server->sid, $this->gameCode, $this->gameName, $server->pname, $server->pid, $this->dbConfig, $curDate, $runTime);
                $task->setInfo($server->sid);
                $task->init();
                $this->platTasks[$server->pid] = $task;
            }
            $data['WorldID'] = $server->sid;
            $data['sign'] = RealCountConfig::getSign($data);
            $params = http_build_query($data);

            $request = new Curl_request (RealCountConfig::$rechargeUrl . $params);
            $curl->add($request);
            $count++;
        }
//        }
        $curl->execute();
        echo $curl->display_errors();
    }

}
