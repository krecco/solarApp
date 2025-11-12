<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SetupRolesAndPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:roles-permissions {--fresh : Reset all roles and permissions before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup roles and permissions for the SaaS Central application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('fresh')) {
            $this->warn('Resetting all roles and permissions...');
            
            // Clear all existing data
            \DB::table('model_has_permissions')->delete();
            \DB::table('model_has_roles')->delete();
            \DB::table('role_has_permissions')->delete();
            Permission::query()->delete();
            Role::query()->delete();
            
            $this->info('Cleared existing roles and permissions.');
        }

        $this->info('Setting up roles and permissions...');

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for System Admin
        $adminPermissions = [
            // User Management
            'view-all-users',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles',
            
            // Tenant Management (Global)
            'view-all-tenants',
            'create-tenants',
            'edit-all-tenants',
            'delete-tenants',
            'suspend-tenants',
            'activate-tenants',
            'provision-tenants',
            
            // Plan Management
            'view-plans',
            'create-plans',
            'edit-plans',
            'delete-plans',
            'manage-plan-features',
            
            // Billing Management (Global)
            'view-all-billing',
            'manage-stripe-webhooks',
            'view-all-subscriptions',
            'cancel-any-subscription',
            'refund-payments',
            
            // System Management
            'view-system-logs',
            'view-sync-queue',
            'retry-sync-jobs',
            'view-provisioning-jobs',
            'view-audit-logs',
            'manage-api-keys',
            'view-system-metrics',
            'manage-system-settings',
        ];

        // Create permissions for Tenant Owners
        $tenantPermissions = [
            // Own Tenant Management
            'view-own-tenants',
            'create-own-tenant',
            'edit-own-tenant',
            'delete-own-tenant',
            
            // Subscription Management
            'view-own-subscription',
            'update-subscription',
            'cancel-subscription',
            'view-billing-history',
            'download-invoices',
            'update-payment-method',
            
            // API Key Management (Own)
            'view-api-keys',
            'create-api-keys',
            'delete-api-keys',
            'rotate-api-keys',
            
            // Profile Management
            'view-profile',
            'edit-profile',
            'change-password',
        ];

        // Create all permissions for web guard
        $this->info('Creating permissions for web guard...');
        $allPermissions = array_merge($adminPermissions, $tenantPermissions);
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
            $this->line("  - Created permission: {$permission}");
        }

        // Create System Admin role
        $this->info('Creating System Admin role...');
        $systemAdmin = Role::firstOrCreate(['name' => 'system-admin', 'guard_name' => 'web']);
        $systemAdmin->syncPermissions(array_merge($adminPermissions, $tenantPermissions));
        $this->line('  - System Admin role created with all permissions');

        // Create Tenant role
        $this->info('Creating Tenant role...');
        $tenant = Role::firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
        $tenant->syncPermissions($tenantPermissions);
        $this->line('  - Tenant role created with tenant permissions');

        // Create API guard permissions
        $this->info('Creating permissions for API guard (sanctum)...');
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'sanctum']
            );
        }

        // Create roles for API guard
        $systemAdminApi = Role::firstOrCreate(['name' => 'system-admin', 'guard_name' => 'sanctum']);
        $systemAdminApi->syncPermissions(Permission::where('guard_name', 'sanctum')->get());

        $tenantApi = Role::firstOrCreate(['name' => 'tenant', 'guard_name' => 'sanctum']);
        $tenantApi->syncPermissions(
            Permission::where('guard_name', 'sanctum')
                ->whereIn('name', $tenantPermissions)
                ->get()
        );

        // Create System Admin user
        $this->info('Creating System Admin user...');
        $admin = User::firstOrCreate(
            ['email' => 'admin@saascentral.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123456'),
                'email_verified_at' => now(),
            ]
        );
        
        // Ensure admin has the correct role
        if (!$admin->hasRole('system-admin')) {
            $admin->assignRole('system-admin');
            $this->line('  - Assigned system-admin role to admin@saascentral.com');
        }

        // Create test users in development
        if (app()->environment(['local', 'development'])) {
            $this->info('Creating test users for development...');
            
            $testUser = User::firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test User',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            if (!$testUser->hasRole('tenant')) {
                $testUser->assignRole('tenant');
                $this->line('  - Created test user: test@example.com (role: tenant)');
            }

            $adminUser = User::firstOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin User',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            if (!$adminUser->hasRole('system-admin')) {
                $adminUser->assignRole('system-admin');
                $this->line('  - Created admin user: admin@example.com (role: system-admin)');
            }
        }

        // Display summary
        $this->newLine();
        $this->info('=== Setup Complete ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Roles', Role::count()],
                ['Permissions', Permission::count()],
                ['System Admins', User::role('system-admin')->count()],
                ['Tenants', User::role('tenant')->count()],
            ]
        );

        $this->newLine();
        $this->info('System Admin Credentials:');
        $this->line('Email: admin@saascentral.com');
        $this->line('Password: admin123456');
        $this->warn('⚠️  Please change the password immediately in production!');
        
        return Command::SUCCESS;
    }
}
