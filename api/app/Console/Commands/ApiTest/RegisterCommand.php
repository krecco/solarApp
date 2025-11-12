<?php

namespace App\Console\Commands\ApiTest;

use App\Models\User;

class RegisterCommand extends BaseApiCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test:register
        {--email= : Email address for the new user}
        {--password= : Password (min 8 characters)}
        {--name= : Full name of the user}
        {--random : Generate random test data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the user registration endpoint (simplified - no tenant)';

    protected string $method = 'POST';
    protected string $endpoint = '/api/v1/register';
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
        if ($this->option('random')) {
            $timestamp = time();
            return [
                'name' => 'Test User ' . $timestamp,
                'email' => 'test.' . $timestamp . '@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ];
        }

        // Validate required fields
        $required = ['email', 'password', 'name'];
        $missing = [];

        foreach ($required as $field) {
            if (!$this->option($field)) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            $this->error('Missing required fields: ' . implode(', ', $missing));
            $this->info("\nExample usage:");
            $this->line('  php artisan api:test:register --email=john@example.com --password=Test123! --name="John Doe"');
            $this->line('  php artisan api:test:register --random');
            exit(1);
        }

        return [
            'name' => $this->option('name'),
            'email' => $this->option('email'),
            'password' => $this->option('password'),
            'password_confirmation' => $this->option('password'),
        ];
    }

    /**
     * Handle token saving from registration response
     */
    protected function handleTokenSaving(array $response): void
    {
        if (isset($response['data']['token'])) {
            $this->saveToken($response['data']['token']);
            $this->info("\n✅ Registration successful!");

            if (isset($response['data']['user'])) {
                $user = $response['data']['user'];
                $this->info("📧 User: {$user['email']}");
                $this->info("👤 Name: {$user['name']}");
                $this->info("👥 Role: " . ($user['role'] ?? 'user'));
            }
        }
    }

    /**
     * Verify database changes after registration
     */
    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $this->info("\n📊 Database Verification:");

        // Check user was created
        if (isset($response['data']['user']['email'])) {
            $user = User::where('email', $response['data']['user']['email'])->first();

            if ($user) {
                $this->info("✅ User created:");
                $this->info("   - ID: {$user->id}");
                $this->info("   - Email: {$user->email}");
                $this->info("   - Name: {$user->name}");
                $this->info("   - Roles: " . $user->getRoleNames()->implode(', '));
                $this->info("   - Email verified: " . ($user->email_verified_at ? 'Yes' : 'No'));

                // Show useful SQL queries
                $this->info("\n🔍 Useful SQL queries:");
                $this->line("   SELECT * FROM users WHERE email = '{$user->email}';");
                $this->line("   SELECT * FROM model_has_roles WHERE model_id = {$user->id};");
            }
        }

        // Show database stats
        $this->showDatabaseStats('users');
    }

    /**
     * Get example payload for documentation
     */
    public function getExamplePayload(): array
    {
        return [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];
    }

    /**
     * Get endpoint description
     */
    public function getEndpointDescription(): string
    {
        return 'Register a new user account without tenant (simplified registration)';
    }
}
