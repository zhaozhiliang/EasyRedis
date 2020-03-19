<?php
namespace EasyRedis\queue;

use EasyRedis\component\Singleton;
use EasyRedis\lock\Lock;
use EasyRedis\queue\abstractInterface\DealMsgAbstract;

class Queue
{
    use Singleton;

    private $dealObjects = [];
    public function addDealObject($type, $className)
    {
        if(!isset($this->dealObjects[$type])){
            $ref = new \ReflectionClass($className);
            if($ref->isSubclassOf(DealMsgAbstract::class)) {
                $this->dealObjects[$type] = $className;
            }
        }
    }

    /**
     * 将任务加入到队列
     * @param \Redis $redis
     * @param string $queueName   此List需要再此之前就存在
     * @param string $type
     * @param array $args
     * @return bool
     */
    public function enter(\Redis $redis, string $queueName, string $type, array $args)
    {
        $data = array(
            'type'=> $type,
            'args' => $args

        );
        return $redis->rPush($queueName, json_encode($data));
    }


    /**
     * 处理队列
     * @param \Redis $redis
     * @param $queue
     */
    public function deal(\Redis $redis, $queue)
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

            if(!isset($this->dealObjects[$type])){
                //记录日志
                echo "消息类型:{$type} 没有配置相应的处理类\n";
                continue;
            }
            $className = $this->dealObjects[$type];
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

            if(!isset($this->dealObjects[$type])){
                //记录日志
                echo "消息类型:{$type} 没有配置相应的处理类\n";
                continue;
            }
            $className = $this->dealObjects[$type];

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
     * @param $delayName
     * @param $queueName
     * @param $type
     * @param $args
     * @param int $time
     * @return int
     */
    public function enterDelayQueue(\Redis $redis, string $delayName, string $queueName, string $type, array $args, int $time)
    {
        $queueMsg = $delayMsg = array(
            'type' => $type,
            'args' => $args
        );
        $delayMsg['queueName'] = $queueName;
        $delayMsgJson = json_encode($delayMsg);

        return $redis->zAdd($delayName, $time, $delayMsgJson);
    }

    /**
     * 处理延迟队列中的任务
     * @param \Redis $redis
     * @param $delayName
     */
    public function dealDelayQueue(\Redis $redis, string $delayName)
    {
        while(true){
            $item = $redis->zRange($delayName, 0, 0, true );
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

            $locked = Lock::acquire($redis,"lock:delay:{$delayName}");
            if (!$locked) {
                continue;
            }

            if ($redis->zRem($delayName, $key1)) {
                $redis->rPush($queue, json_encode($queueMsg));
            }
            Lock::release($redis, "lock:delay:{$delayName}", $locked);
        }
    }



}