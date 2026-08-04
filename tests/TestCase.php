<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Guard the database before anything else runs.
     *
     * When bootstrap/cache/config.php exists, Laravel stops reading environment
     * variables entirely, so the DB_DATABASE override in phpunit.xml is ignored and
     * the suite connects to whatever the cache says. Combined with RefreshDatabase
     * that migrates and truncates the DEVELOPMENT database. That happened twice
     * during this work: once because `artisan optimize` had run, and again because
     * the first version of this guard sat after parent::setUp() -- too late, since
     * RefreshDatabase is triggered from inside it by setUpTraits().
     *
     * So the check runs FIRST, before the application is even created, using the
     * raw environment and the filesystem rather than config().
     */
    protected function setUp(): void
    {
        $this->guardTestDatabase();

        parent::setUp();
    }

    private function guardTestDatabase(): void
    {
        if (file_exists(dirname(__DIR__) . '/bootstrap/cache/config.php')) {
            throw new RuntimeException(
                'The configuration is cached, so phpunit.xml cannot redirect the database and '
                . 'this run would migrate and truncate the development database. '
                . 'Run `php artisan config:clear` before the test suite.'
            );
        }

        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');

        if (! in_array($database, ['memflash_test', ':memory:'], true)) {
            throw new RuntimeException(
                'Tests resolved DB_DATABASE to [' . var_export($database, true) . '] rather than '
                . '[memflash_test]. Refusing to run, because RefreshDatabase would truncate it.'
            );
        }
    }
}
