<?php

namespace Modules\Common\Tests;

use Modules\Common\Enums\BooleanEnum;

class EnumTest extends TestCase
{
    public function testEnum(): void
    {
        $this->assertSame(BooleanEnum::all()->toArray(), ['TRUE' => 1, 'FALSE' => 0]);
        $this->assertSame(BooleanEnum::maps(), ['是', '否']);
    }
}
