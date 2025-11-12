<?php

namespace App\Console\Commands\ApiTest;

class VerifyEmailCommand extends BaseApiCommand
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'api:test:verify-email
                            {--email= : Email address to verify}
                            {--code= : Verification code}
                            {--token= : Verification token}
                            {--authenticated : Use authenticated endpoint}';

    /**
     * The console command description.
     */
    protected $description = 'Verify email address with code or token';

    /**
     * The HTTP method for this endpoint
     */
    protected string $method = 'POST';

    /**
     * The API endpoint path
     */
    protected string $endpoint = '/api/v1/email/verify';

    /**
     * Whether this endpoint requires authentication
     */
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
     * Execute the console command.
     */
    public function handle()
    {
        // Check if using authenticated endpoint
        if ($this->option('authenticated')) {
            $this->endpoint = '/api/v1/email/verify-authenticated';
            $this->requiresAuth = true;
            $this->info("📌 Using authenticated endpoint");
        }

        // Interactive mode if missing required parameters
        if (!$this->option('email') && !$this->option('authenticated')) {
            $email = $this->ask('Enter email address to verify');
            if ($email) {
                $this->input->setOption('email', $email);
            } else {
                $this->error("Email is required (unless using --authenticated flag)");
                return 1;
            }
        }

        if (!$this->option('code') && !$this->option('token')) {
            $this->info("\n📬 Verification Method:");
            $method = $this->choice(
                'How do you have the verification?',
                ['code' => 'I have a 6-digit code', 'token' => 'I have a verification token/link'],
                'code'
            );

            if ($method === 'code') {
                $code = $this->ask('Enter the 6-digit verification code');
                if ($code) {
                    $this->input->setOption('code', $code);
                }
            } else {
                $token = $this->ask('Enter the verification token (or paste the full link)');
                // Extract token if full URL was pasted
                if ($token && (str_contains($token, '?token=') || str_contains($token, '&token='))) {
                    preg_match('/token=([^&]+)/', $token, $matches);
                    $token = $matches[1] ?? $token;
                }
                if ($token) {
                    $this->input->setOption('token', $token);
                }
            }
        }

        return parent::handle();
    }

    /**
     * Prepare request data from command options/arguments
     */
    protected function prepareRequestData(): array
    {
        $data = [];

        if ($this->option('email')) {
            $data['email'] = $this->option('email');
        }

        if ($this->option('code')) {
            $data['code'] = $this->option('code');
        }

        if ($this->option('token')) {
            $data['token'] = $this->option('token');
        }

        return $data;
    }

    /**
     * Handle the API response
     */
    protected function handleResponse(\Illuminate\Http\Client\Response $response, array $requestData): void
    {
        parent::handleResponse($response, $requestData);

        if ($response->successful()) {
            $result = $response->json();
            
            // Show user info if returned
            if (isset($result['data']['user'])) {
                $user = $result['data']['user'];
                $this->newLine();
                $this->info("👤 User Information:");
                $this->line("  Name: " . ($user['name'] ?? 'N/A'));
                $this->line("  Email: " . ($user['email'] ?? 'N/A'));
                $this->line("  Verified: " . ($user['email_verified_at'] ? '✅ Yes' : '❌ No'));
            }
            
            // Show next steps
            $this->newLine();
            $this->info("🎉 Your email has been verified!");
            $this->line("You can now access all features of your account.");
            
        } else {
            // Provide helpful error messages
            $error = $response->json()['message'] ?? $response->json()['error'] ?? null;
            
            if ($error) {
                $this->newLine();
                if (str_contains(strtolower($error), 'expired')) {
                    $this->comment("💡 Tip: Request a new verification code with:");
                    $email = $requestData['email'] ?? 'your-email';
                    $this->comment("  php artisan api:test:resend-verification --email={$email}");
                } elseif (str_contains(strtolower($error), 'invalid')) {
                    $this->comment("💡 Tip: Make sure you entered the correct code/token");
                    $this->comment("Check your email for the latest verification message");
                } elseif (str_contains(strtolower($error), 'already verified')) {
                    $this->comment("✅ This email is already verified!");
                    $this->comment("You can proceed to login.");
                }
            }
        }
    }

    /**
     * Handle token saving from response
     */
    protected function handleTokenSaving(array $response): void
    {
        if (isset($response['data']['token'])) {
            $this->saveToken($response['data']['token']);
            $this->info("🔑 Authentication token received and saved!");
            $this->comment("You are now logged in!");
        }
    }

    /**
     * Verify database changes
     */
    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $this->info("\n📊 Database Changes:");
        $this->line("  - users: Updated email_verified_at timestamp");
        $this->line("  - password_resets: Verification token marked as used/deleted");
        $this->line("  - activity_logs: Email verification event logged");
        
        if ($this->option('email')) {
            $this->newLine();
            $this->line("🔍 Useful SQL queries:");
            $this->line("  SELECT email_verified_at FROM users WHERE email = '{$this->option('email')}';");
            $this->line("  SELECT * FROM activity_logs WHERE log_name = 'email_verification' ORDER BY created_at DESC LIMIT 1;");
        }
    }

    /**
     * Get example payload for documentation
     */
    public function getExamplePayload(): array
    {
        return [
            'email' => 'user@example.com',
            'code' => '123456',
            // OR
            'token' => 'verification-token-string'
        ];
    }

    /**
     * Get endpoint description
     */
    public function getEndpointDescription(): string
    {
        return 'Verifies a user\'s email address using either a 6-digit code or a verification token. ' .
               'Use --authenticated flag to verify the current authenticated user\'s email.';
    }
}
