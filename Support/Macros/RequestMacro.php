<?php

namespace Modules\Common\Support\Macros;

/**
 * @mixin \Illuminate\Http\Request
 */
class RequestMacro
{
    public function userId(): callable
    {
        return fn () => optional($this->user())->id;
    }

    public function headers(): callable
    {
        return function ($key = null, $default = null) {
            return $key === null
                ? collect($this->header())
                    ->map(fn ($header) => $header[0])
                    ->toArray()
                : $this->header($key, $default);
        };
    }
}
