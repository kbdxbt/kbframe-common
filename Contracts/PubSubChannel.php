<?php

declare(strict_types=1);

namespace Modules\Common\Contracts;

interface PubSubChannel
{
    /**
     * 发布消息
     *
     * @param mixed $key
     * @param mixed $message
     * @param mixed $parameter
     */
    public function publish($key, $message, $parameter): mixed;

    /**
     * 订阅消息
     *
     * @param mixed $key
     * @param mixed $callback
     * @param mixed $parameter
     */
    public function consume($key, $callback, $parameter): mixed;
}
