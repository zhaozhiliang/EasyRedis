<?php
require __DIR__ . '/../shared.php';

use EasyRedis\queue\Queue;

function actionRegister()
{
    global $redis;


    for ($i = 1; $i <= 1; $i++) {
        $args = ['email' => 'lbm' . $i . '@lbm.com', 'uid' => $i, 'time' => time()];
        $addRes = Queue::getInstance()->enter($redis, 'queue:important', 'register', $args);
        echo 'addRes:';
        var_dump($addRes);
    }
    $res = $redis->lRange('queue:important', 0, -1);
    echo '<pre>';
    var_dump($res);
    echo '</pre>';

    sleep(3);

    //QueueRedis::dealQueue($this->redis, 'queue:register_email');

}

function actionPaysuccess()
{
    global $redis;

    for($i=1; $i<= 1; $i++){
        $args = ['order_no'=> $i.time() ,'time' => time()];
        $addRes = Queue::getInstance()->enter($redis, 'queue:important','paySuccess',$args);
        echo 'addRes:';
        var_dump($addRes);
    }
    $res = $redis->lRange('queue:important', 0, -1);
    echo '<pre>';
    var_dump($res);
    echo '</pre>';
}

echo __FILE__;

//模拟请求到达控制器后 执行相应的action
//actionRegister();
actionPaysuccess();