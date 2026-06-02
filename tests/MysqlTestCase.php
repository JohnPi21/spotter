<?php

namespace Tests;

use Database\Seeders\TestCatalogSeeder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionRegistrar;

abstract class MysqlTestCase extends BaseTestCase
{
    use DatabaseTransactions;

    protected array $connectionsToTransact = ['mysql_testing'];

    protected static bool $mysqlTestingDatabasePrepared = false;

    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $app['config']->set('database.default', 'mysql_testing');

        if (! self::$mysqlTestingDatabasePrepared) {
            Artisan::call('migrate:fresh', [
                '--database' => 'mysql_testing',
                '--no-interaction' => true,
            ]);

            self::$mysqlTestingDatabasePrepared = true;
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TestCatalogSeeder::class);
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
