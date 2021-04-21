# php-rabbitmq

## 测试demo
```
composer intsall
```
### 1、启动一个消费者
```
php demo_consume.php
```
### 2. 发送一条消息 
```
php demo_publish.php
```

## 安装
```
composer require lib/queue-rabbit
```

## 发布
```
php artisan vendor:publish --provider="Lib\Queue\QueueServiceProvider"
```

## 提供服务# php-rabbitmq

