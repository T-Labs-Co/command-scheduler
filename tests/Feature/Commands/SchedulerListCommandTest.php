<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use TLabsCo\CommandScheduler\Tests\Fixtures\DummyAttributeCommand;

it('runs scheduler:list command successfully', function () {
    Artisan::registerCommand(new DummyAttributeCommand);

    $this->artisan('scheduler:list')
        ->assertSuccessful();
});
