<?php

namespace Modules\Common\Tests\Enums;

use Modules\Common\Enums\BooleanEnum;
use Modules\Common\Tests\TestCase;

class EnumTest extends TestCase
{
    public function testEnum(): void
    {
        $this->assertSame(BooleanEnum::all('map')->toArray(), ['1' => '是', '0' => '否']);
        $this->assertSame(BooleanEnum::values('map'), ['是', '否']);
        $this->assertSame(BooleanEnum::fromValue(1), '是');
    }
}
