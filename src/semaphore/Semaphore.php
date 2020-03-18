<?php
namespace EasyRedis\semaphore;

use EasyRedis\lock\Lock;
class Semaphore
{
    /**
     * 获取信号量
     * @param \Redis $redis
     * @param string $name
     * @param int $limit
     * @param int $timeout
     * @return bool
     */
    public static function acquire(\Redis $redis, string $name, int $limit, int $timeout=10){
        $identifier = Lock::acquire($redis, $name, 0.01);
        if($identifier){
            $res = self::unSafeAcquire($redis, $name, $limit, $timeout);
            Lock::release($redis,$name, $identifier);
            return $res;
        }
        return false;
    }

    private static function unSafeAcquire(\Redis $redis, string $name, int $limit, int $timeout=10){
        $identifier = uuid_create(1);
        $czset = $name.":owner";   //信号拥有集合
        $ctr = $name.':counter';   //计数器

        $now = time();
        $res = $redis->multi(\Redis::PIPELINE)
            ->zRemRangeByScore($name, '-inf', $now - $timeout)
            ->zInterStore($czset, [$czset,$name], [1,0])
            ->incr($ctr)
            ->exec();

        $counter = end($res);     //对计数器自增，并获取计数器在执行自增操作之后的值。

        $res = $redis->multi(\Redis::PIPELINE)
            ->zAdd($name, $now, $identifier)
            ->zAdd($czset, $counter,$identifier)
            ->zRank($czset, $identifier)    //通过检查排名来判断客户端是否取得了信号量
            ->exec();

        if(end($res) < $limit){
            return $identifier;
        }

        //客户端未能取得信号量，清理无用数据。
        $redis->multi(\Redis::PIPELINE)
            ->zRem($name, $identifier)
            ->zRem($czset, $identifier)
            ->exec();
        return false;
    }

    /**
     * 释放信号量
     * @param \Redis $redis
     * @param string $name
     * @param string $identifier
     * @return mixed
     */
    public static function release(\Redis $redis, string $name, string $identifier){
        $res = $redis->multi(\Redis::PIPELINE)
            ->zRem($name, $identifier)
            ->zRem($name.":owner", $identifier)
            ->exec();
        return $res[0];
    }

    /**
     * 刷新信号量
     * @param \Redis $redis
     * @param string $name
     * @param string $identifier
     * @param int $timeout
     * @return bool
     */
    public static function refresh(\Redis $redis, string $name, string $identifier, int $timeout=20){
        $score = $redis->zScore($name, $identifier);
        if($score !== false ){
            if($score < time()- $timeout){
                //该信号量已过期
                echo '信号量已过期';
                self::release($redis, $name, $identifier);
                return false;
            } else {
                if($redis->zAdd($name, time(), $identifier)){
                    echo '释放信号量';
                    self::release($redis, $name, $identifier);
                    return false;
                }
                return true;
            }

        } else {
            echo '不存在信号量';
            return false;
        }

    }


}