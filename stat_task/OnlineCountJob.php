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
use task\realtime\online\OnlineCount;
use task\realtime\RealCountConfig;


include 'httputils\curl.class.php';

class OnlineCountJob extends StatisticJob
{
    private $serverList = null;
    private $callCount = 0;
    /** @var array \task\diablo\OnlineCount */
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
        RealCountConfig::initOnlineUrl($config['servers_url'], $config['online_url'], $config['server_key']);
    }

    public function run()
    {

        $this->readServerList();

        $runTime = date('Y-m-d H:i:s');//脚本开始运行时间,DIP采集基准时间
        $this->beginLog();
        $this->readPlatformData();

        $curDate = $this->startDate;
        $taskStartTime = microtime(true);
        $taskName = 'OnlineCount';

        $this->requestCurl($taskName, $curDate, $runTime);
        Helper::log("[TASK END] $taskName , time elapsed: " . Helper::formatTimeInterval(microtime(true) - $taskStartTime));

        $this->endLog();
    }

    private function readServerList()
    {
        $serverListPath = dirname(__DIR__) . '/serverlist.cache';

        if (file_exists($serverListPath)) {
            $fileModifyTime = filemtime($serverListPath);

            if ($fileModifyTime + 10 * 60000 > time()) {
                //文件未过期
                $serverListData = file_get_contents($serverListPath);
                $this->serverList = json_decode($serverListData);
            }
        }
        if ($this->serverList == null) {

            $sid = 1;
            $httpClient = new AsyncOperation(RealCountConfig::$serversUrl, array(), $sid);
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
            Helper::log("count online fail errorCode:$result");
            return;
        }
        $matches = array();

        parse_str(substr($info['url'], strrpos($info['url'], '?') + 1), $matches);
        $operator = $matches['operator'];
        /** @var OnlineCount $task */
        $task = $this->platTasks[$operator];
        $dtStatDate = $this->findNearFiveMinutes();
        Helper::log('返回时离5分钟附近的时间点：$dtStatDate->' . $dtStatDate);
        $worldPlayerCountList = array();
        $worldTotalRechargeList = array();
        $worldTotalHeroCreateList = array();
        $retWorldList = $decodedJson->WorldList;
        $index = 0;

        foreach ($retWorldList as $worldInfo) {
            $worldPlayerCountList[$index++] = array(
                'iWorldId' => $worldInfo->WorldID,
                'player_count' => $worldInfo->OnlinePlayerCount,
                'account_count' => $worldInfo->OnlineAccountCount
            );
            $worldTotalRechargeList[$index] = array(
                'iWorldId' => $worldInfo->WorldID,
                'totalRecharge' => $worldInfo->TotalRecharge
            );
            $worldTotalHeroCreateList[$index] = array(
                'iWorldId' => $worldInfo->WorldID,
                'playerCount' => $worldInfo->CreatePlayerCount,
                'accountCount' => $worldInfo->CreateAccountCount
            );
        }
        if (count($worldPlayerCountList) > 0) {
            $task->update('online_count', $worldPlayerCountList, $dtStatDate, false);
            $task->update('tbrealrecharge', $worldTotalRechargeList, $dtStatDate, false);
            $task->update('create_count', $worldTotalHeroCreateList, $dtStatDate, false);

        }
    }

    private function requestCurl($taskName, $curDate, $runTime)
    {

        $curl = new Curl ($this, "callback");
        $count = 1;//平台计数
//        for ($i = 0; $i < 1000; $i++) {

        foreach ($this->serverList as $server) {//遍历平台

            $data = array();
            $data['time'] = time();
            $data['operator'] = $server->pid;
            if (!($task = $this->platTasks[$server->pid])) {
                $task = new OnlineCount($taskName . $server->sid, $this->gameCode, $this->gameName, $server->pname, $server->pid, $this->dbConfig, $curDate, $runTime);
                $task->setInfo($server->sid);
                $task->init();
                $this->platTasks[$server->pid] = $task;
            }
            $data['WorldID'] = $server->sid;
            $data['sign'] = RealCountConfig::getSign($data);
            $params = http_build_query($data);
            $request = new Curl_request (RealCountConfig::$onlineUrl . $params);
            $curl->add($request);
            $count++;
        }
//        }
        $curl->execute();
        echo $curl->display_errors();
    }

    private function findNearFiveMinutes()
    {
        $t = time();
        $FIVE_MINUTES = 300;
        $remain = $t % $FIVE_MINUTES;
        $fiveMultiple = intdiv($t, $FIVE_MINUTES);
        if ($remain > $FIVE_MINUTES / 2) {
            $t = ($fiveMultiple + 1) * $FIVE_MINUTES;
        } else {
            $t = ($fiveMultiple) * $FIVE_MINUTES;
        }
        return date("Y-m-d H:i:s", $t);
    }

}
