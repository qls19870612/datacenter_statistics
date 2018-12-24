<?php

$config = array();

$config['db'] = require_once('db.php');
if (file_exists(__DIR__ . '/db_test.php')) {
    $db_test = require_once('db_test.php');
    $config['db'] = array_merge($config['db'], $db_test);
}

$config['task'] = require_once('tasklist.php');
if (file_exists(__DIR__ . '/tasklist_test.php')) {
    $task_test = require_once('tasklist_test.php');
    $config['task'] = array_merge($config['task'], $task_test);
}
$config['servers_url'] = 'http://123.207.115.217:20000/serversByJson';


$config['online_url'] = 'http://localhost:19998/QuerySystem/OnlineCount?';
$config['recharge_url'] = 'http://localhost:19998/QuerySystem/ServerTimeAreaRecharge?';
$config['server_key'] = 'wojiusuibianyongleyigemima123456';

return $config;