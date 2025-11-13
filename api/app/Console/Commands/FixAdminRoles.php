<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class FixAdminRoles extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fix:admin-roles {email?}';

    /**
     * The console command description.
     */
    protected $description = 'Fix admin roles by assigning sanctum guard role';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?? 'admin@saascentral.com';

        $admin = User::where('email', $email)->first();

        if (!$admin) {
            $this->error("User {$email} not found!");
            return 1;
        }

        $this->info("Checking roles for: {$admin->name} ({$admin->email})");
        $this->info("User ID: {$admin->id}");

        // Check current roles
        $webRoles = $admin->getRoleNames('web')->toArray();
        $sanctumRoles = $admin->getRoleNames('sanctum')->toArray();

        $this->info("Current roles (web): " . implode(', ', $webRoles ?: ['none']));
        $this->info("Current roles (sanctum): " . implode(', ', $sanctumRoles ?: ['none']));

        // Ensure admin role exists for both guards
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $sanctumAdminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);

        // Assign admin role to web guard if missing
        if (!$admin->hasRole('admin', 'web')) {
            $admin->assignRole('admin');
            $this->info("✓ Assigned 'admin' role to web guard");
        } else {
            $this->info("✓ User already has 'admin' role on web guard");
        }

        // Assign admin role to sanctum guard if missing
        if (!$admin->hasRole('admin', 'sanctum')) {
            $admin->roles()->attach($sanctumAdminRole);
            $this->info("✓ Assigned 'admin' role to sanctum guard");
        } else {
            $this->info("✓ User already has 'admin' role on sanctum guard");
        }

        // Verify final state
        $this->info("");
        $this->info("Final roles:");
        $this->info("  Web guard: " . implode(', ', $admin->getRoleNames('web')->toArray()));
        $this->info("  Sanctum guard: " . implode(', ', $admin->getRoleNames('sanctum')->toArray()));

        $this->info("");
        $this->info("Role verification:");
        $this->info("  hasRole('admin', 'web'): " . ($admin->hasRole('admin', 'web') ? 'YES' : 'NO'));
        $this->info("  hasRole('admin', 'sanctum'): " . ($admin->hasRole('admin', 'sanctum') ? 'YES' : 'NO'));

        $this->info("");
        $this->info("✅ Admin roles fixed! Please refresh your browser.");

        return 0;
    }
}
