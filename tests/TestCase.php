<?php

namespace Tests;

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
    }
}
