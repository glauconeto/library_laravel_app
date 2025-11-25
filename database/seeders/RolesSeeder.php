<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criando roles
        $reader = Role::create(['name' => 'reader']);
        $librarian = Role::create(['name' => 'librarian']);

        // Atribuindo permissões para os papéis
        $reader->givePermissionTo(['view books', 'borrow books']);
        $librarian->givePermissionTo(['view books', 'borrow books', 'manage books', 'manage loans']); 
    }
}
