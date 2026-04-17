<?php

namespace Niduranga\DevGuard\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Niduranga\DevGuard\DevGuardServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            DevGuardServiceProvider::class,
        ];
    }
}