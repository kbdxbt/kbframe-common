<?php

declare(strict_types=1);

namespace Modules\Common\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class CommaSeparatedToIntegerArrayCast implements CastsAttributes
{
    public function get($model, $key, $value, $attributes): ?array
    {
        return $value ? array_map('intval', explode(',', $value)) : [];
    }

    public function set($model, $key, $value, $attributes)
    {
        return $value;
    }
}
