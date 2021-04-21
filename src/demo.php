<?php

$config = array(
    'host' => '172.18.10.96',
    'vhost' => 'dev',
    'port' => 5672,
    'login' => 'dev',
    'password' => 'dev'
);

$exchange = "commerce.order";


$rabbit = new \Lib\Queue\Services\Rabbit($config, AMQP_EX_TYPE_TOPIC, $exchange);


$suffixKey = ".test";
$rabbit->consume($exchange . $suffixKey,  'callback', '#');

/**
 * 回调函数 即时消息
 * @param $envelope
 * @param $queue
 */
function callback($envelope, $queue)
{
    echo $envelope->getBody();

    $data = json_decode($envelope->getBody(), true); // 消息内容
    $envelopeId = $envelope->getDeliveryTag();
    //处理业务逻辑 成功则确认销毁队列中的消息
    worker($data) ? $queue->ack($envelopeId) : $queue->nack($envelopeId);
}

//处理业务逻辑
function worker($data)
{
    var_dump($data);
    return true;
}