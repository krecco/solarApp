<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SystemAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * SIMPLIFIED VERSION: Uses 'admin' role instead of 'system-admin'
     */
    public function run(): void
    {
        // Create System Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@saascentral.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123456'), // Change this in production!
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role to both web and sanctum guards
        $admin->assignRole('admin'); // web guard (default)
        if (!$admin->hasRole('admin', 'sanctum')) {
            $admin->roles()->attach(\Spatie\Permission\Models\Role::where('name', 'admin')->where('guard_name', 'sanctum')->first());
        }

        $this->command->info('System Admin created:');
        $this->command->info('Email: admin@saascentral.com');
        $this->command->info('Password: admin123456');
        $this->command->warn('⚠️  Please change the password immediately in production!');
    }
}
