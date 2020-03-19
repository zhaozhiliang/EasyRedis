<?php
namespace EasyRedis\queue\abstractInterface;

use EasyRedis\component\SingletonSubclass;
abstract class DealMsgAbstract
{
    use SingletonSubclass;
    abstract function run(array $args);
}