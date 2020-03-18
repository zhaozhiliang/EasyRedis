<?php
namespace EasyRedis\component;

/**
 * 单例 可用在基类或抽象类中，子类调用getInstance()获取，每个子类只有一个实例，各个子类互不影响
 * Trait Singleton
 * @package app\libs\component
 */
trait Singleton
{
    private static $instances = array();

    static function getInstance(...$args)
    {
        if(!isset(self::$instances[static::class])){
            self::$instances[static::class] = new static(...$args);
        }
        return self::$instances[static::class];
    }
}