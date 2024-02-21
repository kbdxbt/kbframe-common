<?php

namespace Modules\Common\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class CurrencyCast implements CastsAttributes
{
    protected int $digits;

    public function __construct(int $digits = 2)
    {
        if ($digits < 1) {
            throw new \InvalidArgumentException('Digits should be a number larger than zero.');
        }

        $this->digits = $digits;
    }

    public function get($model, string $key, $value, array $attributes): ?float
    {
        return $value !== null
            ? round($value / (10 ** $this->digits), $this->digits)
            : null;
    }

    public function set($model, string $key, $value, array $attributes): float|int
    {
        return $value * (10 ** $this->digits);
    }
}
