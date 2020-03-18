<?php
namespace EasyRedis\queue\abstractInterface;

use EasyRedis\component\Singleton;

abstract class DealMsgAbstract
{
    use Singleton;
    abstract function run(array $args);
}