<?php

use Modules\Common\Enums\BooleanEnum;

it('can check enum data', function () {
    expect(BooleanEnum::all('map')->toArray())->toBe([1 => '是', 0 => '否']);
    expect(BooleanEnum::values('map'))->toBe([0 => '是', 1 => '否']);
    expect(BooleanEnum::fromValue(1))->toBe('是');
});
