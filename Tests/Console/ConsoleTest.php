<?php

namespace Modules\Common\Tests\Console;

use Modules\Common\Tests\TestCase;

class ConsoleTest extends TestCase
{
    public function testHealthCheckCommand(): void
    {
        $this->artisan('health:check')->assertExitCode(0);
    }
}
