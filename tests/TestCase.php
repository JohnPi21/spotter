<?php

namespace Tests;

use Database\Seeders\TestCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TestCatalogSeeder::class);

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
