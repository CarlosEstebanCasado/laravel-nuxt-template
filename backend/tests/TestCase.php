<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        /**
         * Test env defaults:
         * - Local/dev: use SQLite in-memory (fast, zero dependencies).
         * - CI: allow overriding to Postgres/Redis by setting env vars in the workflow.
         *
         * We only set defaults when the variable is not already defined.
         */
        if (! $this->envIsSet('DB_CONNECTION')) {
            $this->setEnv('DB_CONNECTION', 'sqlite');
        }

        if (getenv('DB_CONNECTION') === 'sqlite') {
            if (! $this->envIsSet('DB_DATABASE')) {
                $this->setEnv('DB_DATABASE', ':memory:');
            }

            // DB_URL can override DB_CONNECTION/DB_*; clear it for SQLite defaults if not set.
            if (! $this->envIsSet('DB_URL')) {
                $this->setEnv('DB_URL', '');
            }
        }

        if (! $this->envIsSet('SESSION_DRIVER')) {
            $this->setEnv('SESSION_DRIVER', 'array');
        }

        parent::setUp();
    }

    private function envIsSet(string $key): bool
    {
        $value = getenv($key);

        if ($value !== false && $value !== '') {
            return true;
        }

        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return true;
        }

        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return true;
        }

        return false;
    }

    private function setEnv(string $key, string $value): void
    {
        putenv($key.'='.$value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
