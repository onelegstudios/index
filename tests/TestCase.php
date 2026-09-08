<?php

declare(strict_types=1);

namespace Onelegstudios\Index\Tests;

use Onelegstudios\Index\IndexServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            IndexServiceProvider::class,
        ];
    }
}
