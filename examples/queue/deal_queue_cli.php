<?php
require __DIR__.'/../shared.php';

use EasyRedis\queue\Queue;

use EasyRedis\Example\queue\dealmsg\PaySuccessMsg;
use EasyRedis\Example\queue\dealmsg\RegisterMsg;
function actionSingle($queueName)
{
    global $redis;
    Queue::getInstance()->addDealObject('paySuccess', PaySuccessMsg::class);
    Queue::getInstance()->addDealObject('register', RegisterMsg::class);
    Queue::getInstance()->deal($redis, $queueName);
}


actionSingle('queue:important');