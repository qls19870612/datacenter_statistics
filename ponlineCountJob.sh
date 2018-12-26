#!/bin/bash
sftp root@192.168.1.99 <<EOF
cd /usr/local/server/php_statistics/
lcd D:/workspace/DataCenter/statistics/stat_task
put -r -P ./OnlineCountJob.php
cd /usr/local/server/php_statistics/config
put -r -P ./config/config.php
exit
close
bye
EOF

