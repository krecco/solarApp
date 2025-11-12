<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates roles from config file
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = config('roles.available');
        $guards = config('roles.guards');

        foreach ($roles as $roleName) {
            foreach ($guards as $guardName) {
                Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guardName]);
            }
        }

        $this->command->info('Roles created: ' . implode(', ', $roles));
    }
}
