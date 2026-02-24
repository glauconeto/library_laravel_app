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

        $reader->givePermissionTo(['view books', 'borrow books']);
        $librarian->givePermissionTo(['view books', 'borrow books', 'manage books', 'manage loans']);
    }
}
