<?php

namespace Modules\Common\Support\PubSubChannel;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class AmqpClient extends BasePubSubChannel
{
    protected AMQPStreamConnection $connection;

    protected AMQPChannel $channel;

    protected array $queueInfo;

    protected array $config = [];

    protected array $properties = [];

    protected int $messageCount = 0;

    public function __construct($name)
    {
        $this->config = config('amqp.connections.'.$name);
        if (empty($this->config)) {
            throw new \RuntimeException('amqp connections is not define!');
        }

        $this->connect();
    }

    /**
     * @throws \Exception
     */
    protected function setup(): void
    {
        $exchange = $this->getProperty('exchange', 'default');

        if (empty($exchange)) {
            throw new \Exception('Please check your settings, exchange is not defined.');
        }

        $this->channel->exchange_declare(
            $exchange,
            $this->getProperty('exchange_type', 'direct'),
            $this->getProperty('exchange_passive', false),
            $this->getProperty('exchange_durable', true),
            $this->getProperty('exchange_auto_delete', false),
            $this->getProperty('exchange_internal', false),
            $this->getProperty('exchange_nowait', false),
            $this->getProperty('exchange_properties', [])
        );

        $queue = $this->getProperty('queue');

        if (! empty($queue) || $this->getProperty('queue_force_declare')) {
            $this->queueInfo = $this->getChannel()->queue_declare(
                $queue,
                $this->getProperty('queue_passive', false),
                $this->getProperty('queue_durable', true),
                $this->getProperty('queue_exclusive', false),
                $this->getProperty('queue_auto_delete', false),
                $this->getProperty('queue_nowait', false),
                new AMQPTable($this->getProperty('queue_properties', []))
            );

            foreach ((array) $this->getProperty('routing') as $routingKey) {
                $this->getChannel()->queue_bind(
                    $queue ?: $this->queueInfo[0],
                    $exchange,
                    $routingKey
                );
            }
        }
        // clear at shutdown
        $this->connection->set_close_on_destruct(true);
    }

    /**
     * 连接
     */
    public function connect(): void
    {
        if (! empty($this->config['ssl_options'])) {
            $this->connection = new AMQPSSLConnection(
                $this->getConnectConfig('host'),
                $this->getConnectConfig('port'),
                $this->getConnectConfig('username'),
                $this->getConnectConfig('password'),
                $this->getConnectConfig('vhost'),
                $this->getConnectConfig('ssl_options'),
                $this->getConnectConfig('connect_options')
            );
        } else {
            $this->connection = new AMQPStreamConnection(
                $this->getConnectConfig('host'),
                $this->getConnectConfig('port'),
                $this->getConnectConfig('username'),
                $this->getConnectConfig('password'),
                $this->getConnectConfig('vhost'),
                $this->getConnectConfig('insist', false),
                $this->getConnectConfig('login_method', 'AMQPLAIN'),
                $this->getConnectConfig('login_response', null),
                $this->getConnectConfig('locale', 3),
                $this->getConnectConfig('connection_timeout', 3.0),
                $this->getConnectConfig('read_write_timeout', 130),
                $this->getConnectConfig('context', null),
                $this->getConnectConfig('keepalive', false),
                $this->getConnectConfig('heartbeat', 60),
                $this->getConnectConfig('channel_rpc_timeout', 0.0),
                $this->getConnectConfig('ssl_protocol', null)
            );
        }

        $this->channel = $this->connection->channel();
    }

    /**
     * 关闭所有的连接和通道
     */
    public function close(): void
    {
        $this->getChannel()->close();
        $this->connection->close();
    }

    /**
     * @throws \Exception
     */
    public function init(array $properties = []): void
    {
        $this->mergeProperties($properties)
            ->setup();
    }

    /**
     * @param mixed $queue
     * @param mixed $message
     *
     * @throws \Exception
     */
    public function publish($queue, $message, array $properties = [], bool $mandatory = false): bool
    {
        empty($properties['queue']) && $properties['queue'] = $queue;
        empty($properties['routing']) && $properties['routing'] = 'default';
        $this->mergeProperties($properties)
            ->setup();

        $exchange = $this->getProperty('exchange', 'default');
        $routing = $this->getProperty('routing', '');

        if (($message instanceof AMQPMessage) === false) {
            $properties = [
                'content_type' => 'text/plain',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ];
            if ($message_properties = $this->getProperty('message_properties', [])) {
                $properties = array_merge($message_properties, $properties);
            }
            $message = new AMQPMessage($message, $properties);
        }

        if (true === $mandatory) {
            $this->getChannel()->confirm_select();
            $this->getChannel()->set_nack_handler([$this, 'nack']);
            $this->getChannel()->set_return_listener([$this, 'return']);
        }

        $timeout = $this->getProperty('publish_timeout') > 0
            ? $this->getProperty('publish_timeout')
            : 30;

        $this->getChannel()->basic_publish($message, $exchange, $routing, $mandatory);
        true === $mandatory && $this->getChannel()->wait_for_pending_acks_returns((int) $timeout);

        return true;
    }

    /**
     * @param mixed $queue
     * @param mixed $callback
     *
     * @throws \Exception
     */
    public function consume($queue, $callback, array $properties = []): bool
    {
        try {
            empty($properties['queue']) && $properties['queue'] = $queue;
            $this->mergeProperties($properties)
                ->setup();

            $this->messageCount = $this->getQueueMessageCount();

            if (! $this->getProperty('persistent', true) && 0 === $this->messageCount) {
                return true;
            }

            $object = $this;

            if ($this->getProperty('qos')) {
                $this->getChannel()->basic_qos(
                    $this->getProperty('qos_prefetch_size', 0),
                    $this->getProperty('qos_prefetch_count', 1),
                    $this->getProperty('qos_a_global', false)
                );
            }

            $this->getChannel()->basic_consume(
                $queue,
                $this->getProperty('consumer_tag', ''),
                $this->getProperty('consumer_no_local', false),
                $this->getProperty('consumer_no_ack', false),
                $this->getProperty('consumer_exclusive', false),
                $this->getProperty('consumer_nowait', false),
                function ($message) use ($callback, $object): void {
                    $callback($message, $object);
                },
                null,
                $this->getProperty('consumer_properties', [])
            );

            // consume
            while (\count($this->getChannel()->callbacks)) {
                $this->getChannel()->wait(
                    null,
                    false,
                    $this->getProperty('timeout') ?: 0
                );
            }
        } catch (\Exception $e) {
            if ($e instanceof AMQPTimeoutException) {
                return true;
            }

            throw $e;
        }

        return true;
    }

    /**
     * Acknowledges a message
     */
    public function acknowledge(AMQPMessage $message): void
    {
        $message->getChannel()->basic_ack($message->getDeliveryTag());

        if ('quit' === $message->body) {
            $message->getChannel()->basic_cancel($message->getConsumerTag());
        }
    }

    /**
     * Rejects a message and requeues it if wanted (default: false)
     */
    public function reject(AMQPMessage $message, bool $requeue = false): void
    {
        $message->getChannel()->basic_reject($message->getDeliveryTag(), $requeue);
    }

    /**
     * @param mixed $queue
     */
    public function size($queue): mixed
    {
        $channel = $this->getChannel();
        [, $size] = $channel->queue_declare($queue, true);
        $channel->close();

        return $size;
    }

    /**
     * @param mixed $queue
     */
    public function delete($queue): mixed
    {
        $this->getChannel()->queue_delete($queue);
    }

    public function getQueueMessageCount(): int
    {
        if (\is_array($this->queueInfo)) {
            return $this->queueInfo[1];
        }

        return 0;
    }

    /**
     * @since 2.12.0
     */
    public function getChannel(): ?self
    {
        return $this->channel;
    }

    /**
     * @return $this
     */
    public function mergeProperties(array $properties)
    {
        $this->properties = array_merge($this->properties, $properties);

        return $this;
    }

    /**
     * @param mixed $key
     * @param  null  $default
     * @return null|mixed
     */
    public function getProperty($key, $default = null): mixed
    {
        return \array_key_exists($key, $this->properties) ? $this->properties[$key] : $default;
    }

    /**
     * @param mixed $key
     * @param  null  $default
     * @return null|mixed
     */
    public function getConnectConfig($key, $default = null): mixed
    {
        return \array_key_exists($key, $this->config) ? $this->config[$key] : $default;
    }
}
