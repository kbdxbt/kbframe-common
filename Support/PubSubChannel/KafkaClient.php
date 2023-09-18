<?php

namespace Modules\Common\Support\PubSubChannel;

class KafkaClient extends BasePubSubChannel
{
    protected array $brokerList;

    public function __construct($name)
    {
        $this->brokerList = config('kafka.connections.'.$name.'.broker_list');
        if (empty($this->brokerList)) {
            throw new \RuntimeException('kafka broker list is not define!');
        }
    }

    /**
     * 生产数据
     *
     * @param mixed $topic
     * @param mixed $message
     */
    public function publish($topic, $message, array $properties = []): bool
    {
        $conf = new \RdKafka\Conf();
        // 绑定服务节点
        $conf->set('metadata.broker.list', $this->brokerList);
        // 设置参数
        foreach ($properties as $k => $v) {
            $conf->set($k, $v);
        }

        // 创建生产者
        $kafka = new \RdKafka\Producer($conf);

        // 创建主题实例
        $topic = $kafka->newTopic($topic);
        // 生产主题数据，此时消息在缓冲区中，并没有真正被推送
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
        // 阻塞时间(毫秒)， 0为非阻塞
        $kafka->poll(0);

        // 推送消息，如果不调用此函数，消息不会被发送且会丢失
        $result = $kafka->flush(5000);

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            throw new \RuntimeException('Was unable to flush, messages might be lost!');
        }

        return true;
    }

    /**
     * 消费数据
     *
     * @param mixed $topic
     * @param mixed $callback
     *
     * @throws \Exception
     */
    public function consume($topic, $callback, array $properties = []): bool
    {
        $conf = new \RdKafka\Conf();
        $properties = array_merge([
            'metadata.broker.list' => $this->brokerList,
            'group.id' => 'default',
            'enable.auto.commit' => 'false',
            'topic.metadata.refresh.interval.ms' => 60000,
            'socket.keepalive.enable' => 'true',
        ], $properties);
        // 设置参数
        foreach ($properties as $k => $v) {
            $conf->set($k, $v);
        }

        // 创建消费者实例
        $consumer = new \RdKafka\KafkaConsumer($conf);
        // 消费者订阅主题，数组形式
        $consumer->subscribe([$topic]);
        for (;;) {
            // 消费数据，阻塞1秒
            $message = $consumer->consume(1000);

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $callback(...[$message, $consumer]);

                    break;

                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    throw new \Exception("No more messages; will wait for more\n");

                    break;

                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    // throw new \Exception("Timed out\n");
                    break;

                default:
                    throw new \Exception($message->errstr(), $message->err);

                    break;
            }
        }

        return true;
    }
}
