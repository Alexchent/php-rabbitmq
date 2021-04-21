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

$rabbit = new \Lib\Queue\Services\Amqp($config, "topic", $exchange);

$binding_keys = array_slice($argv, 1);
$suffixKey = ".test2";
$rabbit->consume($exchange . $suffixKey,  'callback', $binding_keys);

function callback($msg)
{
    echo ' [x] ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";
}
