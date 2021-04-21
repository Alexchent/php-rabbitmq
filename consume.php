<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('172.18.10.96', 5672, 'dev', 'dev','dev');
$channel = $connection->channel();

$exchange = "commerce.order";
$channel->exchange_declare($exchange, 'topic', false, true, false);

list($queue_name, ,) = $channel->queue_declare($exchange.'.test2', false, true, true, false);

$binding_keys = array_slice($argv, 1);
if (empty($binding_keys)) {
    echo("Usage: $argv[0] [binding_key]\n");
    exit(1);
}

foreach ($binding_keys as $binding_key) {
    $channel->queue_bind($queue_name, $exchange, $binding_key);
}

echo " [*] Waiting for logs. To exit press CTRL+C\n";

$callback = function ($msg) {
    echo ' [x] ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
