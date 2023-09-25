<?php

use Modules\Common\Enums\BooleanEnum;

it('can check enum data', function () {
    $this->assertSame(BooleanEnum::all('map')->toArray(), ['1' => '是', '0' => '否']);
    $this->assertSame(BooleanEnum::values('map'), ['是', '否']);
    $this->assertSame(BooleanEnum::fromValue(1), '是');
});