<?php
require __DIR__ . '/../shared.php';
use EasyRedis\semaphore\Semaphore;

function actionTest()
{
    global $redis;
    for($i=0; $i< 10; $i++){
        $res = Semaphore::acquire($redis, 'mysqlConnector', 10, 20);
        var_dump($res);echo "\n";
//            if($i == 1){
//                Semaphore::release($this->redis, 'mysqlConnector', $res);
//            }
    }
}

//模拟请求到达控制器后 执行相应的action
actionTest();