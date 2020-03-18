<?php
require __DIR__.'/../shared.php';

use EasyRedis\queue\Queue;

function actionSingle($queueName)
{
    global $redis;
    Queue::deal($redis, $queueName);
}


actionSingle('queue:important');