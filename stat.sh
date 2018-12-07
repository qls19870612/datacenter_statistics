#!/usr/bin/env bash
phpPath=C:/PHP/php.exe
scriptPath=D:/workspace/DataCenter/statistics/stat_task/
startDate=0
endDate=1
echo start
$phpPath ${scriptPath}main.php diablo_cn daily $startDate $endDate
echo enter