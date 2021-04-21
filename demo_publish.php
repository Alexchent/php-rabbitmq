<?php
require_once __DIR__."/vendor/autoload.php";

$config = array(
    'host' => '172.18.10.96',
    'vhost' => 'dev',
    'port' => 5672,
    'login' => 'dev',
    'password' => 'dev'
);

$exchange = "commerce.order";

$rabbit = new \Lib\Queue\Services\Rabbit($config, AMQP_EX_TYPE_TOPIC, $exchange);

$rabbit->publish('2222','#');