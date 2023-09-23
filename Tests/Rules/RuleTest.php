<?php

namespace Modules\Common\Tests\Rules;

use Illuminate\Support\Facades\Validator;
use Modules\Common\Rules\DefaultRule;
use Modules\Common\Tests\TestCase;

class RuleTest extends TestCase
{
    public function testChineseWordRule(): void
    {
        $validator = Validator::make(['title' => '测试中文'], [
            'title' => 'chinese_word',
        ]);

        $this->assertSame($validator->fails(), false);
    }

    public function testDefaultRule(): void
    {
        $validator = Validator::make([], [
            'title' => [new DefaultRule('test')],
        ]);

        $this->assertSame($validator->fails(), false);
        $this->assertSame($validator->getData(), ['title' => 'test']);
    }
}
