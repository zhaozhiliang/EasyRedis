<?php

namespace EasyRedis\queue;

use EasyRedis\lock\Lock;

class Queue
{

    /**
     * 将任务加入到队列
     * @param \Redis $redis
     * @param string $queueName   此List需要再此之前就存在
     * @param string $type
     * @param array $args
     * @return bool
     */
    public static function enter(\Redis $redis, string $queueName, string $type, array $args)
    {
        $data = array(
            'type'=> $type, //'do_goods_email',
            'args' => $args

        );
        return $redis->rPush($queueName, json_encode($data));
    }


    /**
     * 处理队列
     * @param \Redis $redis
     * @param $queue
     */
    public static function deal(\Redis $redis, $queue)
    {
        while(true){

            $packed = $redis->blpop($queue, 30); //阻塞30秒

            if (!$packed) {
                echo "no packed \n";
                continue;
            }

            $params = json_decode($packed[1], true);
            $type = $params['type'];
            $args = $params['args'];

            //print_r($params);

            $className = 'EasyRedis\queue\Deal'.ucfirst($type).'Msg';

            if(!class_exists($className)){
                //记录日志
                echo "class:{$className}不存在\n";
                continue;
            }

            $className::getInstance()->run($args);
        }
    }


    /**
     * 处理多个队列
     * @param \Redis $redis
     * @param $queues
     */
    public function dealQueues(\Redis $redis, array $queues)
    {
        while(true){
            $packed = $redis->blpop($queues, 30); //阻塞30秒

            if (!$packed) {
                echo "no packed \n";
                continue;
            }


            $params = json_decode($packed[1], true);
            $type = $params['type'];
            $args = $params['args'];
//            var_dump($name);
//            var_dump($args);

            $className = 'EasyRedis\queue'.ucfirst($type).'Msg';
            if(!class_exists($className)){
                //记录日志
                echo "class:{$className}不存在\n";
                continue;
            }

            $className::getInstance()->run($args);
        }
    }

    /**
     * 将任务添加到 延迟任务队列中
     * @param \Redis $redis
     * @param $queueName
     * @param $type
     * @param $args
     * @param int $delay
     * @return int
     */
    public static function enterDelayQueue(\Redis $redis, string $queueName, string $type, array $args, int $delay=0)
    {

        $queueMsg = $delayMsg = array(
            'type' => $type,
            'args' => $args
        );
        $delayMsg['queueName'] = $queueName;
        $delayMsgJson = json_encode($delayMsg);
        if($delay > 0){
            return $redis->zAdd('delayed:', time()+ $delay, $delayMsgJson);
        } else {
            return $redis->rPushX($queueName, json_encode($queueMsg));
        }

    }

    /**
     * 处理延迟队列中的任务
     * @param $redis
     */
    public static function dealDelayQueue(\Redis $redis)
    {
        while(true){
            $item = $redis->zRange('delayed:', 0, 0, true );
            //var_dump($item);
            if (!$item || end($item) > time()) {
                usleep(100000);  //睡眠100毫秒
                continue;
            }
            $key_item = array_keys($item);
            $key1 = array_pop($key_item);
            $params = json_decode($key1, true);

            $queue = $params['queueName'];
            $queueMsg = array(
                'type' => $params['type'],
                'args' => $params['args']
            );

            $locked = Lock::acquire($redis,'lock:delayed');

            if (!$locked) {
                continue;
            }

            if ($redis->zRem('delayed:', $key1)) {
                echo 'add queue:activity;';
                $redis->rPush($queue, json_encode($queueMsg));
            }

            Lock::release($redis, 'lock:delayed', $locked);

        }
    }



}