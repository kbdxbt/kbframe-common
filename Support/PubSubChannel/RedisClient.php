<?php

namespace Modules\Common\Support\PubSubChannel;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

class RedisClient extends BasePubSubChannel
{
    protected Connection $connection;

    protected function __construct(?string $name = null)
    {
        $this->connection = Redis::connection($name);
    }

    public function publish($key, $message, $parameter)
    {
        return $this->connection->publish($key, $message);
    }

    public function consume($key, $message, $parameter): void
    {
        $this->connection->psubscribe($key, $message);
    }
}
