<?php

declare(strict_types=1);

use TLabsCo\CommandScheduler\Core\Scheduling\Application\Discovery\AttributeSchedulerDiscovery;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;
use TLabsCo\CommandScheduler\Tests\Fixtures\DummyAttributeCommand;

it('discovers schedule from attribute reflection', function () {
    $discovery = new AttributeSchedulerDiscovery;
    $reflection = new ReflectionClass(DummyAttributeCommand::class);

    $method = new ReflectionMethod($discovery, 'extractInfo');
    $method->setAccessible(true);
    $info = $method->invoke($discovery, $reflection);

    expect($info)->toBeArray()
        ->and($info['class'])->toBe(DummyAttributeCommand::class)
        ->and($info['signature'])->toBe('test:dummy-attribute')
        ->and($info['attribute']['expression'])->toBe('0 2 * * *')
        ->and($info['attribute']['run_window'])->toBe(RunWindow::WORKING_HOURS->value)
        ->and($info['attribute']['metadata']['category'])->toBe('maintenance')
        ->and($info['attribute']['priority'])->toBe(10)
        ->and($info['attribute']['environments'])->toBe(['production', 'testing'])
        ->and($info['attribute']['metadata']['tags'])->toBe(['test', 'dummy']);
});
