<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        /** @var PermissionRegistrar $perm */
        $perm = App::make(PermissionRegistrar::class);
        $perm->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);
    }
}
