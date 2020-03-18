<?php
namespace EasyRedis\lock;


class Lock
{

    /**
     * 完善的分布式锁 - 带有超时限制特性
     * @param \Redis $redis
     * @param $lockName
     * @param int $acquireTimeout
     * @param int $lockTimeout
     * @return bool|string
     */
    public static function acquire(\Redis $redis, string $lockName, int $acquireTimeout=5, int $lockTimeout=20){
        //$identifier = uniqid();
        $identifier = uuid_create(1);
        $lock_name = 'lock:'.$lockName;
        $end = time() + $acquireTimeout;

        while(time() < $end){
            if($redis->set($lock_name,$identifier, ['nx', 'ex'=>$lockTimeout])){
                return $identifier;
            }else if( -1 === $redis->ttl($lock_name)){   //-1 没有设置过期时间
                $redis->expire($lock_name, $lockTimeout);
            }

            usleep(1000);  //1000微妙== 1毫秒
        }
        return false;
    }

    /**
     * 释放锁
     * @param \Redis $redis
     * @param $lockName
     * @param $identifier
     * @return bool
     */
    public static function release(\Redis $redis, string $lockName, string $identifier){
        //$this->redis->pipeline();  //会引起错误？
        $lock_name = 'lock:'.$lockName;

        while(true){
            $redis->watch($lock_name);
            if($redis->get($lock_name) == $identifier){
                $redis->multi()
                    ->del([$lock_name])
                    ->exec();
                return true;
            }
            $redis->unwatch();
            break;
        }

        return false;
    }

}