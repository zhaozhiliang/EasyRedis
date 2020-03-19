<?php
require __DIR__.'/../shared.php';

use EasyRedis\queue\Queue;

use EasyRedis\Example\queue\dealmsg\PaySuccessMsg;
use EasyRedis\Example\queue\dealmsg\RegisterMsg;
function actionSingle($queueName)
{
    global $redis;
    $queue = Queue::getInstance();
    $queue->addDealObject('paySuccess', PaySuccessMsg::class);
    $queue->addDealObject('register', RegisterMsg::class);
    $queue->deal($redis, $queueName);
}


actionSingle('queue:important');