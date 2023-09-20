<?php

namespace Modules\Common\Enums;

use Modules\Common\Support\Traits\EnumConcern;

enum StatusEnum: int
{
    use EnumConcern;

    case ENABLED = 1;
    case DISABLED = 0;
    case DELETE = -1;

    public function map(): string
    {
        return match ($this) {
            self::ENABLED => '启用',
            self::DISABLED => '禁用',
            self::DELETE => '删除',
        };
    }
}
