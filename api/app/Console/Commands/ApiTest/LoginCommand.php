<?php

namespace App\Console\Commands\ApiTest;

use App\Models\User;

class LoginCommand extends BaseApiCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test:login 
        {--email= : Email address}
        {--password= : Password}
        {--save-as= : Save token with a custom name (e.g., admin-bearer.txt)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the login endpoint';

    protected string $method = 'POST';
    protected string $endpoint = '/api/v1/login';
    protected bool $requiresAuth = false;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->configureBaseOptions();
    }

    /**
     * Prepare request data from command options/arguments
     */
    protected function prepareRequestData(): array
    {
        $email = $this->option('email');
        $password = $this->option('password');

        if (!$email || !$password) {
            $this->error('Email and password are required!');
            $this->info("\nExample usage:");
            $this->line('  php artisan api:test:login --email=john@example.com --password=Password123!');
            $this->line('  php artisan api:test:login --email=admin@example.com --password=AdminPass123! --save-as=admin-bearer.txt');
            exit(1);
        }

        return [
            'email' => $email,
            'password' => $password,
        ];
    }

    /**
     * Handle token saving from login response
     */
    protected function handleTokenSaving(array $response): void
    {
        if (isset($response['data']['token'])) {
            // Check if we should save to a custom file
            if ($this->option('save-as')) {
                $customFile = base_path('cli_tests/' . $this->option('save-as'));
                \Illuminate\Support\Facades\File::put($customFile, $response['data']['token']);
                $this->info("\n🔑 Token saved to: " . $this->option('save-as'));
            } else {
                // Determine if this is an admin login
                $isAdmin = false;
                if (isset($response['data']['user']['role'])) {
                    $isAdmin = $response['data']['user']['role'] === 'admin';
                }
                
                $this->saveToken($response['data']['token'], $isAdmin);
            }

            $this->info("\n✅ Login successful!");
            
            if (isset($response['data']['user'])) {
                $user = $response['data']['user'];
                $this->info("📧 User: {$user['email']}");
                $this->info("👤 Name: {$user['name']}");
                $this->info("🎭 Role: {$user['role']}");
                
                if (isset($user['tenant'])) {
                    $this->info("🏢 Tenant: {$user['tenant']['subdomain']}.yoursaas.com");
                }
            }

            // Show token preview
            $token = $response['data']['token'];
            $this->info("🔑 Token: " . substr($token, 0, 30) . "..." . substr($token, -10));
        }
    }

    /**
     * Verify database state after login
     */
    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $this->info("\n📊 Database Check:");

        // Check user last login time was updated
        if (isset($response['data']['user']['email'])) {
            $user = User::where('email', $response['data']['user']['email'])->first();
            
            if ($user) {
                $this->info("✅ User verified:");
                $this->info("   - ID: {$user->id}");
                $this->info("   - Last login: " . ($user->last_login_at ?? 'Not tracked'));
                $this->info("   - Login count: " . ($user->login_count ?? 'Not tracked'));
                
                // Check if user has active sessions
                $activeSessions = \DB::table('personal_access_tokens')
                    ->where('tokenable_id', $user->id)
                    ->where('tokenable_type', User::class)
                    ->count();
                    
                $this->info("   - Active sessions: {$activeSessions}");
                
                // Show useful queries
                $this->info("\n🔍 Useful SQL queries:");
                $this->line("   -- Check user's active tokens");
                $this->line("   SELECT * FROM personal_access_tokens WHERE tokenable_id = {$user->id} AND tokenable_type = 'App\\\\Models\\\\User';");
                $this->line("   -- Check user details");
                $this->line("   SELECT * FROM users WHERE id = {$user->id};");
            }
        }
    }

    /**
     * Get example payload for documentation
     */
    public function getExamplePayload(): array
    {
        return [
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ];
    }

    /**
     * Get endpoint description
     */
    public function getEndpointDescription(): string
    {
        return 'Authenticate a user and receive a bearer token for subsequent API requests. The token should be included in the Authorization header as "Bearer {token}" for authenticated endpoints.';
    }
}
