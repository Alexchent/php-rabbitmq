<?php


namespace Lib\Queue\Services;


use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Amqp
{
    private $connect;
    private $channel;
    private $exchange;
    private $queue;

    const EXCHANGE_NAME = 'exchange';

    /**
     * Rabbit constructor.
     * @param $config
     * array(
     * 'host' => '192.168.31.231',
     * 'port' => '5672',
     * 'login' => 'admin',
     * 'password' => 'admin',
     * 'vhost' => 'vhost',
     * )
     * @param string $exchangeType
     * @param string $exchangeName
     */
    public function __construct($config, $exchangeType = 'direct', $exchangeName=self::EXCHANGE_NAME)
    {

        $this->connect = new AMQPStreamConnection($config['host'], $config['port'], $config['login'], $config['password'], $config['vhost']);
//        $this->connect = new AMQPStreamConnection('172.18.10.96', 5672, 'dev', 'dev','dev');
        $this->channel = $this->connect->channel();
        $this->exchange = $exchangeName;
        $this->channel->exchange_declare($exchangeName, $exchangeType, false, true, false);
    }

    public function declareExchange($exchangeName, $type)
    {
        $this->channel->exchange_declare($exchangeName, $type, false, true, false);
    }

    /**
     * @param $queueName
     * @param array $routing_keys 路由键
     */
    public function declareQueue($queueName, $routing_keys)
    {
        list($queue_name, ,) = $this->channel->queue_declare($queueName, false, true, true, false);

        foreach ($routing_keys as $binding_key) {
            $this->channel->queue_bind($queue_name, $this->exchange, $binding_key);
        }
    }

    public function publish($message, $routeKey)
    {
        $msg = new AMQPMessage($message);
        $this->channel->basic_publish($msg, $this->exchange, $routeKey);
    }

    public function consume($queueName, $callback, $routingKey)
    {
        $this->declareQueue($queueName, $routingKey);

        $this->channel->basic_consume($queueName, '', false, true, false, false, $callback);

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }

        $this->channel->close();
        $this->connect->close();
    }

    public function callback($msg)
    {
        echo ' [x] ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";
    }
}