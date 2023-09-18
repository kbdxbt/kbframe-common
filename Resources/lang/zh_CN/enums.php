<?php

use Modules\Common\Enums\BooleanEnum;
use Modules\Common\Enums\StatusEnum;

return [
    StatusEnum::class => [
        StatusEnum::ENABLED => '启用',
        StatusEnum::DISABLED => '禁用',
        StatusEnum::DELETE => '已删除',
    ],
    BooleanEnum::class => [
        BooleanEnum::TRUE => '是',
        BooleanEnum::FALSE => '否',
    ],
];
