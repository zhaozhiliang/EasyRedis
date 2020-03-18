<?php
namespace EasyRedis\queue;

use EasyRedis\queue\abstractInterface\DealMsgAbstract;

class DealPaySuccessMsg extends DealMsgAbstract
{
    function run(array $args)
    {
        echo "成功处理了订单号为：{$args['order_no']}的订单!\n";
    }
}