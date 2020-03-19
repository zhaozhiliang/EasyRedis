<?php
require __DIR__.'/../shared.php';

use EasyRedis\queue\Queue;


function actionDelay()
{
    global $redis;
    $queue = Queue::getInstance();
    $queue->dealDelayQueue($redis, 'delay:queue:important');
}

actionDelay();