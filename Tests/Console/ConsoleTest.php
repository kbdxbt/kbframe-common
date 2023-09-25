<?php

it('can exec health check command', function () {
    $this->artisan('health:check')->assertExitCode(0);
});
