<?php
namespace app;

error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Shanghai');
ini_set('memory_limit', '4000M');
//set_time_limit(20 * 3600);//20小时

define("DEBUG", true);
define("ROOTPATH", __DIR__);

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'AutoLoader.php');

$config = require_once(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'StatisticJob.php');

$app = new StatisticJob($config);
$app->run();