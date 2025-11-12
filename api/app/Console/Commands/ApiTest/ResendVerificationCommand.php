<?php

namespace App\Console\Commands\ApiTest;

class ResendVerificationCommand extends BaseApiCommand
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'api:test:resend-verification
                            {--email= : Email address to resend verification to}
                            {--authenticated : Use authenticated endpoint}';

    /**
     * The console command description.
     */
    protected $description = 'Resend email verification code/link';

    /**
     * The HTTP method for this endpoint
     */
    protected string $method = 'POST';

    /**
     * The API endpoint path
     */
    protected string $endpoint = '/api/v1/email/resend';

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
            $this->endpoint = '/api/v1/email/resend-authenticated';
            $this->requiresAuth = true;
            $this->info("📌 Using authenticated endpoint (current user)");
        }

        // If no email provided and not authenticated, ask for it
        if (!$this->option('email') && !$this->option('authenticated')) {
            $email = $this->ask('Enter email address to resend verification to');
            if ($email) {
                $this->input->setOption('email', $email);
            } else {
                $this->error("Email is required (unless using --authenticated flag)");
                return 1;
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
            
            $this->newLine();
            $this->info("📬 Next Steps:");
            $this->line("1. Check your email for the verification message");
            $this->line("2. Click the verification link or copy the code");
            
            $email = $requestData['email'] ?? 'your-email';
            $this->line("3. Run: php artisan api:test:verify-email --email={$email} --code=YOUR_CODE");
            
            // Show additional info if provided
            if (isset($result['data'])) {
                $this->newLine();
                if (isset($result['data']['email_sent_to'])) {
                    $this->line("📧 Email sent to: " . $result['data']['email_sent_to']);
                }
                
                if (isset($result['data']['expires_in'])) {
                    $this->line("⏱️  Code expires in: " . $result['data']['expires_in']);
                }
                
                if (isset($result['data']['throttle_seconds'])) {
                    $this->line("⏳ Next resend available in: " . $result['data']['throttle_seconds'] . " seconds");
                }
            }
        } else {
            // Provide helpful error messages
            $error = $response->json()['message'] ?? $response->json()['error'] ?? null;
            
            if ($error) {
                $this->newLine();
                if (str_contains(strtolower($error), 'already verified')) {
                    $this->comment("✅ This email is already verified!");
                    $email = $requestData['email'] ?? 'your-email';
                    $this->comment("You can log in with: php artisan api:test:login --email={$email}");
                } elseif (str_contains(strtolower($error), 'throttle')) {
                    $this->comment("⏳ Please wait before requesting another verification email");
                    $this->comment("Check your spam folder for the previous email");
                } elseif (str_contains(strtolower($error), 'not found')) {
                    $this->comment("❌ No account found with this email");
                    $email = $requestData['email'] ?? 'your-email';
                    $this->comment("Register first with: php artisan api:test:register --email={$email}");
                }
            }
        }
    }

    /**
     * Verify database changes
     */
    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $this->info("\n📊 Database Changes:");
        $this->line("  - email_verifications: New verification token/code created");
        $this->line("  - activity_logs: Email resend event logged");
        $this->line("  - rate_limits: Updated throttle counter");
        
        if ($this->option('email')) {
            $this->newLine();
            $this->line("🔍 Useful SQL queries:");
            $this->line("  SELECT * FROM password_resets WHERE email = '{$this->option('email')}' ORDER BY created_at DESC LIMIT 1;");
            $this->line("  SELECT * FROM activity_logs WHERE log_name = 'email_verification' ORDER BY created_at DESC LIMIT 1;");
        }
    }

    /**
     * Get example payload for documentation
     */
    public function getExamplePayload(): array
    {
        return [
            'email' => 'user@example.com'
        ];
    }

    /**
     * Get endpoint description
     */
    public function getEndpointDescription(): string
    {
        return 'Resends the email verification code/link to the specified email address. ' .
               'Use --authenticated flag to resend to the currently authenticated user.';
    }
}
