<?php
require __DIR__ . '/../shared.php';

use EasyRedis\queue\Queue;

function actionActivity()
{
    global $redis;
    for ($i = 1; $i <= 1; $i++) {
        $args = ['msg' => '八点半十分准时活动直播请大家关注', 'receive' => 'all', 'time' => time()];

        $addRes = Queue::getInstance()->enterDelayQueue($redis, 'delay:queue:important',
            'queue:important', 'activityPush', $args , time()+30);
        echo 'addRes:';
        var_dump($addRes);
    }

}

//模拟请求到达控制器后 执行相应的action
actionActivity();