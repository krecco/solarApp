<?php

namespace App\Console\Commands\ApiTest;

class UserCommand extends BaseApiCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the get current user endpoint (requires authentication)';

    protected string $method = 'GET';
    protected string $endpoint = '/api/v1/user';
    protected bool $requiresAuth = true;

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
        // No request data needed for GET user
        return [];
    }

    /**
     * Verify database state
     */
    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        if (isset($response['data'])) {
            $user = $response['data'];
            
            $this->info("\n📊 User Details:");
            $this->info("✅ Authenticated User:");
            $this->info("   - ID: {$user['id']}");
            $this->info("   - Name: {$user['name']}");
            $this->info("   - Email: {$user['email']}");
            $this->info("   - Role: {$user['role']}");
            $this->info("   - Email Verified: " . (isset($user['email_verified_at']) && $user['email_verified_at'] ? 'Yes' : 'No'));
            
            if (isset($user['tenant'])) {
                $this->info("\n🏢 Tenant Information:");
                $this->info("   - ID: {$user['tenant']['id']}");
                $this->info("   - Name: {$user['tenant']['company_name']}");
                $this->info("   - Subdomain: {$user['tenant']['subdomain']}");
                $this->info("   - Status: {$user['tenant']['status']}");
                
                if (isset($user['tenant']['plan'])) {
                    $this->info("\n💳 Subscription Plan:");
                    $this->info("   - Plan: {$user['tenant']['plan']['name']}");
                    $this->info("   - Price: \${$user['tenant']['plan']['price']}/{$user['tenant']['plan']['billing_period']}");
                }
            }

            // Show related data queries
            $this->info("\n🔍 Related data queries:");
            $this->line("   -- User's permissions");
            $this->line("   SELECT * FROM model_has_permissions WHERE model_id = {$user['id']} AND model_type = 'App\\\\Models\\\\User';");
            $this->line("   -- User's roles");
            $this->line("   SELECT * FROM model_has_roles WHERE model_id = {$user['id']} AND model_type = 'App\\\\Models\\\\User';");
            if (isset($user['tenant_id'])) {
                $this->line("   -- Tenant details");
                $this->line("   SELECT * FROM tenants WHERE id = {$user['tenant_id']};");
            }
        }
    }

    /**
     * Get example payload for documentation
     */
    public function getExamplePayload(): array
    {
        return [];
    }

    /**
     * Get endpoint description
     */
    public function getEndpointDescription(): string
    {
        return 'Get the currently authenticated user\'s details including their profile information, tenant details, and subscription plan. This endpoint requires a valid bearer token.';
    }
}
