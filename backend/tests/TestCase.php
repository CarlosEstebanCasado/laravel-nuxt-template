<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // When running tests inside Docker, environment variables from docker-compose
        // can override phpunit.xml values. Force testing DB/session drivers here
        // before the application is booted.
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        putenv('DB_URL=');
        putenv('SESSION_DRIVER=array');

        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_ENV['DB_URL'] = '';
        $_ENV['SESSION_DRIVER'] = 'array';

        // Laravel's env repository can read from $_SERVER too (depending on adapters),
        // so make sure it matches what we set in putenv/$_ENV.
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';
        $_SERVER['DB_URL'] = '';
        $_SERVER['SESSION_DRIVER'] = 'array';

        parent::setUp();
    }
}
