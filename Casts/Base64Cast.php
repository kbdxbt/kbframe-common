<?php

namespace Modules\Common\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Base64Cast implements CastsAttributes
{
    private bool $isCastGet;

    private bool $isCastSet;

    public function __construct(bool $isCastGet = true, bool $isCastSet = false)
    {
        $this->isCastGet = $isCastGet;
        $this->isCastSet = $isCastSet;
    }

    public function get($model, string $key, mixed $value, array $attributes)
    {
        return $this->isCastGet ? base64_encode($value) : $value;
    }

    public function set($model, string $key, mixed $value, array $attributes)
    {
        return $this->isCastSet ? base64_decode($value) : $value;
    }
}
