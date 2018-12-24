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

    public static $onlineUrl;
    public static $rechargeUrl;
    public static $key;
    public static $serversUrl;

    public static function initOnlineUrl($serversUrl, $url, $key)
    {
        RealCountConfig::$onlineUrl = $url;
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