<?php

namespace Lib\Queue\Services;

/**
 * Rabbit AMQP封装
 *
 * @author Alexchen
 * 使用方法:
 * 1. 发送
 *      new Rabbit()->publish();
 *
 * 2. 处理
 *      new Rabbit()->consume();
 *
 */
class Rabbit
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
     * )
     * @param string $exchangeType
     * @param string $exchangeName
     * @throws \AMQPConnectionException
     */
    public function __construct($config, $exchangeType = AMQP_EX_TYPE_DIRECT, $exchangeName=self::EXCHANGE_NAME)
    {
        $this->connect = new \AMQPConnection($config);
        if (!$this->connect->connect()) {
            echo "Cannot connect to the broker";
            exit();
        }
        $this->channel = new \AMQPChannel($this->connect);
        $this->declareExchange($exchangeName, $exchangeType);
    }

    public function declareExchange($exchangeName, $type)
    {
        //创建一个交换机
        $this->exchange = new \AMQPExchange($this->channel);
        //设置交换机名称
        $this->exchange->setName($exchangeName);
        //设置交换机类型
        //AMQP_EX_TYPE_DIRECT:直连交换机
        //AMQP_EX_TYPE_FANOUT:扇形交换机
        //AMQP_EX_TYPE_HEADERS:头交换机
        //AMQP_EX_TYPE_TOPIC:主题交换机
        $this->exchange->setType($type);
        //设置交换机持久
        $this->exchange->setFlags(AMQP_DURABLE);
        //声明交换机
        $this->exchange->declareExchange();
    }

    /**
     * @param $queueName
     * @param null $routingKey 路由键
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPQueueException
     */
    public function declareQueue($queueName, $routingKey=null)
    {
        //声明路由键
        //创建一个消息队列
        $this->queue = new \AMQPQueue($this->channel);
        //设置队列名称
        $this->queue->setName($queueName);
        //设置队列持久
        $this->queue->setFlags(AMQP_DURABLE);
        //声明消息队列
        $this->queue->declareQueue();
        //交换机和队列通过$routingKey进行绑定
        $this->queue->bind($this->exchange->getName(), $routingKey ?? $queueName);
    }

    public function publish($queueName, $message)
    {
        $this->exchange->publish($message, $queueName);
    }

    public function consume($queueName, $callback, $routingKey = "#")
    {
        $this->declareQueue($queueName, $routingKey);
        while (true) {
            $this->queue->consume($callback);
        }
    }

    public function callback($envelope, $queue)
    {
        $message = $envelope->getBody();
        $queue->nack($envelope->getDeliveryTag());
    }
}
