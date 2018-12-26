<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/14/014
 * Time: 20:52
 */

namespace task\realtime;

class RealCountConfig
{

    public static $onlinePath;
    public static $onlineHost;
    public static $rechargeUrl;
    public static $key;
    public static $serversUrl;

    public static function initOnlineUrl($serversUrl, $onlineHost, $onlinePath, $key)
    {
        RealCountConfig::$onlinePath = $onlinePath;
        RealCountConfig::$onlineHost = $onlineHost;
        RealCountConfig::$key = $key;
        RealCountConfig::$serversUrl = $serversUrl;
    }

    public static function initRechargeUrl($serversUrl, $url, $key)
    {
        RealCountConfig::$rechargeUrl = $url;
        RealCountConfig::$key = $key;
        RealCountConfig::$serversUrl = $serversUrl;
    }


    public static function getSign(array $data)
    {
        $str = 'time=' . $data['time'] . '&key=' . RealCountConfig::$key . '&operator=' . $data['operator'];
        return md5($str);
    }


}