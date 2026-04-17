<?php

use Illuminate\Support\Facades\File;
use function Pest\Laravel\artisan;

it('fails if the action class does not exist', function () {
    artisan('guard:test NonExistentAction')
        ->assertExitCode(0)
        ->expectsOutputToContain('Action class not found at');
});

it('detects the correct test framework from composer.json', function () {
    $this->assertTrue(class_exists(\Niduranga\DevGuard\Commands\GenerateTestCommand::class));
});