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

        // Book permissions
        Permission::findOrCreate('view books', $guard);
        Permission::findOrCreate('manage books', $guard);
        
        // Loan permissions
        Permission::findOrCreate('view loans', $guard);
        Permission::findOrCreate('manage loans', $guard);
        
        // Feedback permissions
        Permission::findOrCreate('create feedback', $guard);
        Permission::findOrCreate('manage feedback', $guard);
    }
}
