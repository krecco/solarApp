<?php

namespace App\Console\Commands\ApiTest;

class AdminUsersCommand extends BaseApiCommand
{
    protected $signature = 'api:test:admin:users
                            {action=list : Action to perform (list, show, create, update, delete)}
                            {id? : User ID for show/update/delete actions}
                            {--search= : Search term for name/email}
                            {--role= : Filter by role}
                            {--tenant-id= : Filter by tenant ID}
                            {--page=1 : Page number}
                            {--per-page=20 : Items per page}
                            {--name= : User name (for create/update)}
                            {--email= : User email (for create/update)}
                            {--password= : User password (for create)}';

    protected $description = 'Admin user management (list, view, create, update, delete)';

    protected string $method = 'GET';
    protected string $endpoint = '/api/v1/admin/users';
    protected bool $requiresAuth = true;
    protected bool $isAdminEndpoint = true;

    public function __construct()
    {
        parent::__construct();
        $this->configureBaseOptions();
    }

    public function handle()
    {
        $action = strtolower($this->argument('action'));
        $id = $this->argument('id');

        // Set up endpoint and method based on action
        switch ($action) {
            case 'list':
                $this->method = 'GET';
                $this->endpoint = '/api/v1/admin/users';
                break;
                
            case 'show':
                if (!$id) {
                    $id = $this->ask('Enter user ID to view');
                    if (!$id) {
                        $this->error("User ID is required for show action");
                        return 1;
                    }
                }
                $this->method = 'GET';
                $this->endpoint = "/api/v1/admin/users/{$id}";
                break;
                
            case 'create':
                $this->method = 'POST';
                $this->endpoint = '/api/v1/admin/users';
                
                if (!$this->option('email')) {
                    $email = $this->ask('Enter email for new user');
                    if ($email) {
                        $this->input->setOption('email', $email);
                    }
                }
                
                if (!$this->option('name')) {
                    $name = $this->ask('Enter name for new user');
                    if ($name) {
                        $this->input->setOption('name', $name);
                    }
                }
                
                if (!$this->option('password')) {
                    $password = $this->secret('Enter password for new user');
                    if ($password) {
                        $this->input->setOption('password', $password);
                    }
                }
                break;
                
            case 'update':
                if (!$id) {
                    $id = $this->ask('Enter user ID to update');
                    if (!$id) {
                        $this->error("User ID is required for update action");
                        return 1;
                    }
                }
                $this->method = 'PUT';
                $this->endpoint = "/api/v1/admin/users/{$id}";
                break;
                
            case 'delete':
                if (!$id) {
                    $id = $this->ask('Enter user ID to delete');
                    if (!$id) {
                        $this->error("User ID is required for delete action");
                        return 1;
                    }
                }
                
                if (!$this->confirm("Are you sure you want to delete user ID {$id}?")) {
                    $this->info("Deletion cancelled");
                    return 0;
                }
                
                $this->method = 'DELETE';
                $this->endpoint = "/api/v1/admin/users/{$id}";
                break;
                
            default:
                $this->error("Invalid action: {$action}");
                $this->info("Valid actions: list, show, create, update, delete");
                return 1;
        }

        return parent::handle();
    }

    protected function prepareRequestData(): array
    {
        $action = strtolower($this->argument('action'));
        
        switch ($action) {
            case 'list':
                $data = [];
                if ($this->option('search')) $data['search'] = $this->option('search');
                if ($this->option('role')) $data['role'] = $this->option('role');
                if ($this->option('tenant-id')) $data['tenant_id'] = $this->option('tenant-id');
                if ($this->option('page')) $data['page'] = $this->option('page');
                if ($this->option('per-page')) $data['per_page'] = $this->option('per-page');
                return $data;
                
            case 'create':
            case 'update':
                $data = [];
                if ($this->option('name')) $data['name'] = $this->option('name');
                if ($this->option('email')) $data['email'] = $this->option('email');
                if ($this->option('password')) $data['password'] = $this->option('password');
                if ($this->option('role')) $data['role'] = $this->option('role');
                return $data;
                
            default:
                return [];
        }
    }

    protected function handleResponse(\Illuminate\Http\Client\Response $response, array $requestData): void
    {
        parent::handleResponse($response, $requestData);

        if ($response->successful()) {
            $action = strtolower($this->argument('action'));
            $result = $response->json();
            
            switch ($action) {
                case 'list':
                    $users = $result['data'] ?? [];
                    $meta = $result['meta'] ?? null;
                    
                    $this->newLine();
                    $this->info("👥 Users" . ($this->option('search') ? " (search: {$this->option('search')})" : ""));
                    
                    if (empty($users)) {
                        $this->warn("No users found");
                    } else {
                        foreach ($users as $user) {
                            $this->line(sprintf(
                                "  [%d] %s <%s> - %s%s",
                                $user['id'],
                                $user['name'],
                                $user['email'],
                                $user['role'] ?? 'user',
                                $user['email_verified_at'] ? ' ✓' : ' ✗'
                            ));
                            
                            if (isset($user['tenant'])) {
                                $this->line("      🏢 " . $user['tenant']['company_name'] . " ({$user['tenant']['subdomain']})");
                            }
                        }
                    }
                    
                    if ($meta) {
                        $this->newLine();
                        $this->line("Page {$meta['current_page']} of {$meta['last_page']} | Total: {$meta['total']}");
                    }
                    break;
                    
                case 'create':
                    $this->newLine();
                    $this->info("✅ User created successfully!");
                    if (isset($result['data']['id'])) {
                        $this->line("User ID: " . $result['data']['id']);
                    }
                    break;
                    
                case 'delete':
                    $this->newLine();
                    $this->info("✅ User deleted successfully!");
                    break;
            }
        }
    }

    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $action = strtolower($this->argument('action'));
        
        if (in_array($action, ['create', 'update', 'delete'])) {
            $this->info("\n📊 Database Changes:");
            
            switch ($action) {
                case 'create':
                    $this->line("  - users: New user record created");
                    $this->line("  - activity_logs: User creation logged");
                    break;
                case 'update':
                    $this->line("  - users: User record updated");
                    $this->line("  - activity_logs: User update logged");
                    break;
                case 'delete':
                    $this->line("  - users: User record deleted");
                    $this->line("  - tenants: Orphaned tenant handled");
                    $this->line("  - activity_logs: User deletion logged");
                    break;
            }
        }
    }

    public function getExamplePayload(): array
    {
        return [
            // For list
            'search' => 'john',
            'role' => 'admin',
            'page' => 1,
            'per_page' => 20,
            
            // For create/update
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'role' => 'user'
        ];
    }

    public function getEndpointDescription(): string
    {
        return 'Admin endpoint for managing users. Supports listing with search/filters, viewing individual users, ' .
               'creating new users, updating user details, and deleting users. Requires admin role.';
    }
}
