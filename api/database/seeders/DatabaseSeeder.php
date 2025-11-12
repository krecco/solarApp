<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Creates roles, settings, and test users with different roles
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolesAndPermissionsSeeder::class);

        // Create system admin
        $this->call(SystemAdminSeeder::class);

        // Seed default settings
        $this->call(SettingSeeder::class);

        // Create test users in development
        if (app()->environment(['local', 'development'])) {
            // Create test manager
            $manager = User::factory()->create([
                'name' => 'Test Manager',
                'email' => 'manager@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $manager->assignRole('manager');

            // Create test regular user
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'user@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('user');

            // Create additional test admin
            $testAdmin = User::factory()->create([
                'name' => 'John Doe Admin',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $testAdmin->assignRole('admin');

            $this->command->info('');
            $this->command->info('Test users created with roles:');
            $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->command->info('ADMIN:   john@example.com / password');
            $this->command->info('MANAGER: manager@example.com / password');
            $this->command->info('USER:    user@example.com / password');
            $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->command->info('');

            // Optionally seed demo data
            if ($this->command->confirm('Would you like to create demo data (solar plants, investments)?', true)) {
                $this->call(DemoDataSeeder::class);
            }
        }
    }
}
