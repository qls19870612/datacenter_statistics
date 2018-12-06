#!/bin/bash
phpPath=C:/PHP/php.exe
scriptPath=D:/workspace/DataCenter/statistics/stat_task/
startDate=2018-11-20
endDate=2018-11-20

taskArr=(
common/AccountInfo
common/DailyBasic
common/DailyLTV
common/DailyRetention
common/MonthlyBasic
common/ShopSummary
)
for task in ${taskArr[*]};do
	echo ""
	echo task:$task
	$phpPath ${scriptPath}main.php diablo_cn $task $startDate $endDate
done


echo �����������
read -n 1
echo ��������