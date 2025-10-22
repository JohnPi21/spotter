<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;
use Database\Seeders\TestCatalogSeeder;

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
