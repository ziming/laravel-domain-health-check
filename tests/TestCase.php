<?php

namespace Ziming\LaravelDomainHealthCheck\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Rdap\RdapServiceProvider;
use Ziming\LaravelDomainHealthCheck\LaravelDomainHealthCheckServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }
}
