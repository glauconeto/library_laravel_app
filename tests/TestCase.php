<?php

namespace Tests;

use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Isolate tests from the dev database: force the testing database in
        // every env source, since $_SERVER values from the container have
        // priority over phpunit.xml's "force" directives in Laravel's
        // Dotenv-based env resolving.
        putenv('DB_DATABASE=testing');
        $_ENV['DB_DATABASE'] = 'testing';
        $_SERVER['DB_DATABASE'] = 'testing';

        parent::setUp();

        // Seed the roles/permissions every test relies on (RefreshDatabase
        // recreates the scheme, so the Spatie tables start empty).
        $this->seed([
            PermissionsSeeder::class,
            RolesSeeder::class,
        ]);
    }
}
