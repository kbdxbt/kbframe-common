<?php

declare(strict_types=1);

namespace Modules\Common\Support\Traits;

trait Singletonable
{
    protected function __construct(...$parameters)
    {
    }

    protected function __clone()
    {
    }

    final public function __wakeup(): void
    {
    }

    public static function instance(...$parameters)
    {
        app()->singletonIf(static::class, fn () => new static(...$parameters));

        return app(static::class);
    }
}
