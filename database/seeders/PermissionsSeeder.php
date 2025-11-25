<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'view books']);
        Permission::create(['name' => 'borrow books']);
        Permission::create(['name' => 'manage books']);
        Permission::create(['name' => 'manage loans']);
    }
}
