<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Tests;

use LumenSistemas\Lgpd\LgpdServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [LgpdServiceProvider::class];
    }
}
