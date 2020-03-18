<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

$single_server = array(
    'host' => '127.0.0.1',
    'port' => 6379,
    'database' => 15,
);

$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);


$multiple_servers = array(
    array(
       'host' => '127.0.0.1',
       'port' => 6379,
       'database' => 15,
       'alias' => 'first',
    ),
    array(
       'host' => '127.0.0.1',
       'port' => 6380,
       'database' => 15,
       'alias' => 'second',
    ),
);
