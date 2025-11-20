<?php

namespace Ziming\LaravelDomainHealthCheck\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Rdap\RdapServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            RdapServiceProvider::class,
        ];
    }
}
