<?php

namespace Modules\Common\Enums;

use Modules\Common\Support\Traits\EnumConcern;

enum BooleanEnum: int
{
    use EnumConcern;

    case TRUE = 1;
    case FALSE = 0;

    public function map(): string
    {
        return match ($this) {
            self::TRUE => '是',
            self::FALSE => '否',
        };
    }
}
