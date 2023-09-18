<?php

namespace Modules\Common\Support\Traits;

trait CreateStaticable
{
    public static function create(...$parameters)
    {
        return static::new(...$parameters);
    }

    public static function make(...$parameters)
    {
        return static::new(...$parameters);
    }

    public static function new(...$parameters)
    {
        return new static(...$parameters);
    }
}
