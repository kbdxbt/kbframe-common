<?php

use Illuminate\Support\Facades\Validator;
use Modules\Common\Rules\DefaultRule;

it('can validate chinese word rule', function () {
    $validator = Validator::make(['title' => '测试中文'], [
        'title' => 'chinese_word',
    ]);

    $this->assertSame($validator->fails(), false);
});

it('can validate default rule', function () {
    $validator = Validator::make([], [
        'title' => [new DefaultRule('test')],
    ]);

    $this->assertSame($validator->fails(), false);
    $this->assertSame($validator->getData(), ['title' => 'test']);
});
