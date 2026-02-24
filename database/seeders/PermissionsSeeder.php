<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        Permission::findOrCreate('view books', $guard);
        Permission::findOrCreate('borrow books', $guard);
        Permission::findOrCreate('manage books', $guard);
        Permission::findOrCreate('manage loans', $guard);
    }
}
