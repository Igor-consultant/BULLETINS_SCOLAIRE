<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'administration',
            'direction',
            'enseignant',
            'comptabilite',
            'parent',
        ] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
    }
}
