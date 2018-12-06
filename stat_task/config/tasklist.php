<?php

return array(
    //每个任务具体执行的内容
    'diablo_cn' => array(
        'daily' => array(
            'common/AccountInfo',
            'common/DailyBasic',
            'common/DailyRetention',
            'common/DailyLTV',
            'common/MonthlyBasic',
            'common/ShopSummary'
        ),
        'temp' => array(
            'common/AccountInfo',
        )
    ),

);
