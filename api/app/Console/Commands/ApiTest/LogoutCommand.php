<?php

namespace App\Console\Commands\ApiTest;

class LogoutCommand extends BaseApiCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test:logout 
        {--clear-token : Clear the saved bearer token after logout}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the logout endpoint (requires authentication)';

    protected string $method = 'POST';
    protected string $endpoint = '/api/v1/logout';
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
        // No request data needed for logout
        return [];
    }

    /**
     * Handle response - clear token if requested
     */
    protected function handleResponse(\Illuminate\Http\Client\Response $response, array $requestData): void
    {
        parent::handleResponse($response, $requestData);

        if ($response->successful() && $this->option('clear-token')) {
            $file = $this->isAdminEndpoint ? $this->adminTokenFile : $this->tokenFile;
            if (\Illuminate\Support\Facades\File::exists($file)) {
                \Illuminate\Support\Facades\File::delete($file);
                $this->info("\n🗑️  Cleared saved token from: " . basename($file));
            }
        }
    }

    /**
     * Verify database changes after logout
     */
    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $this->info("\n📊 Session Check:");
        
        // Get the token that was just used
        $token = $this->getToken();
        if ($token) {
            // Check if token was revoked
            $tokenRecord = \DB::table('personal_access_tokens')
                ->where('token', hash('sha256', explode('|', $token)[1] ?? $token))
                ->first();
                
            if (!$tokenRecord) {
                $this->info("✅ Token successfully revoked");
            } else {
                $this->warn("⚠️  Token may still be active in database");
            }
        }

        $this->info("\n💡 Tips:");
        $this->info("   - The logout endpoint should revoke the current token");
        $this->info("   - Use --clear-token to remove the saved token file");
        $this->info("   - After logout, authenticated requests should fail with 401");
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
        return 'Logout the current user and revoke their authentication token. After logout, the token should no longer be valid for making authenticated requests.';
    }
}
