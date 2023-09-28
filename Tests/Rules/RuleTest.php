<?php

use Illuminate\Support\Facades\Validator;
use Modules\Common\Rules\DefaultRule;

it('can validate chinese word rule', function () {
    $validator = Validator::make(['title' => '测试中文'], [
        'title' => 'chinese_word',
    ]);

    expect($validator->fails())->toBeFalse();
});

it('can validate default rule', function () {
    $validator = Validator::make([], [
        'title' => [new DefaultRule('test')],
    ]);

    expect($validator->fails())->toBeFalse();
    expect($validator->getData())->toBe(['title' => 'test']);
});
