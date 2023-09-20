<?php

namespace Modules\Common\Tests;

class ConsoleTest extends TestCase
{
    public function testHealthCheckCommand(): void
    {
        $this->artisan('health:check')->assertExitCode(0);
    }
}
