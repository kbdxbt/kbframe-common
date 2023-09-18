<?php

namespace Modules\Common\Support\PubSubChannel;

use Modules\Common\Contracts\PubSubChannel;

abstract class BasePubSubChannel implements PubSubChannel
{
    protected static object $instance;

    protected string $name;

    protected function __construct($name)
    {
        $this->name = $name;
    }

    protected function __clone()
    {
    }

    public static function instance($name)
    {
        if (empty(self::$instance[$name])) {
            self::$instance[$name] = new static($name);
        }

        return self::$instance[$name];
    }
}
