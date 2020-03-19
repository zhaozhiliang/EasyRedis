<?php


namespace EasyRedis\Example\queue\dealmsg;


use EasyRedis\queue\abstractInterface\DealMsgAbstract;

class ActivityPushMsg extends DealMsgAbstract
{

    function run(array $args)
    {
        echo "推送一条活动消息:{$args['msg']}\n";
    }
}