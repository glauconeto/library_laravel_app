<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        // Criando roles
        $reader = Role::findOrCreate('reader', $guard);
        $librarian = Role::findOrCreate('librarian', $guard);

        // Reader permissions
        $reader->givePermissionTo([
            'view books',
            'view loans',
            'create feedback',
            'manage feedback'
        ]);
        
        // Librarian permissions (all)
        $librarian->givePermissionTo([
            'view books',
            'manage books',
            'view loans',
            'manage loans',
            'create feedback',
            'manage feedback'
        ]);
    }
}
