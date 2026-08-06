<?php

namespace Tests;

use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function seedRolesAndPermissions(): void
    {
        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);
    }
}
