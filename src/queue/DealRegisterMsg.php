<?php
namespace EasyRedis\queue;

use EasyRedis\queue\abstractInterface\DealMsgAbstract;

class DealRegisterMsg extends DealMsgAbstract
{
    /**
     * @param array $args
     * args 中字段如下
     * email 用户邮箱地址
     *
     */
    function run(array $args)
    {
        //发送注册邮件
        $email = $args['email'] ?? '';
        echo '发送一封注册邮件给：'.$email."\n";
    }
}