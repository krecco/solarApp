<?php

namespace App\Console\Commands\ApiTest;

class VerifyOtpCommand extends BaseApiCommand
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'api:test:otp:verify
                            {--email= : Email address}
                            {--otp= : OTP code}
                            {--purpose=login : Purpose of OTP (login, verification, etc.)}';

    /**
     * The console command description.
     */
    protected $description = 'Verify OTP code';

    /**
     * The HTTP method for this endpoint
     */
    protected string $method = 'POST';

    /**
     * The API endpoint path
     */
    protected string $endpoint = '/api/v1/otp/verify';

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
        // Interactive mode if missing parameters
        if (!$this->option('email')) {
            $email = $this->ask('Enter your email address');
            if ($email) {
                $this->input->setOption('email', $email);
            } else {
                $this->error("Email is required");
                return 1;
            }
        }

        if (!$this->option('otp')) {
            $this->info("\n📧 Check your email for the OTP code");
            $otp = $this->ask('Enter the OTP code from your email');
            if ($otp) {
                $this->input->setOption('otp', $otp);
            } else {
                $this->error("OTP code is required");
                return 1;
            }
        }

        if (!$this->option('purpose')) {
            $purpose = $this->choice(
                'What is this OTP for?',
                ['login', 'verification', 'password_reset'],
                'login'
            );
            $this->input->setOption('purpose', $purpose);
        }

        return parent::handle();
    }

    /**
     * Prepare request data from command options/arguments
     */
    protected function prepareRequestData(): array
    {
        return [
            'email' => $this->option('email'),
            'otp' => $this->option('otp'),
            'purpose' => $this->option('purpose') ?? 'login'
        ];
    }

    /**
     * Handle the API response
     */
    protected function handleResponse(\Illuminate\Http\Client\Response $response, array $requestData): void
    {
        parent::handleResponse($response, $requestData);

        if ($response->successful()) {
            $result = $response->json();
            
            // Show user info if available
            if (isset($result['data']['user'])) {
                $user = $result['data']['user'];
                $this->newLine();
                $this->info("👤 User Information:");
                $this->line("  Name: " . ($user['name'] ?? 'N/A'));
                $this->line("  Email: " . ($user['email'] ?? 'N/A'));
                $this->line("  Role: " . ($user['role'] ?? 'N/A'));
                
                if (isset($user['tenant'])) {
                    $this->line("  Company: " . ($user['tenant']['company_name'] ?? 'N/A'));
                }
            }
            
            // Show next steps
            if (isset($result['data']['next_step'])) {
                $this->newLine();
                $this->info("📋 Next Step:");
                $this->line($result['data']['next_step']);
            }
        } else {
            // Provide helpful error messages
            $error = $response->json()['message'] ?? $response->json()['error'] ?? null;
            
            if ($error) {
                $this->newLine();
                if (str_contains(strtolower($error), 'expired')) {
                    $this->comment("⏰ This OTP has expired");
                    $email = $requestData['email'];
                    $this->comment("Request a new one with: php artisan api:test:otp:send --email={$email}");
                } elseif (str_contains(strtolower($error), 'invalid') || str_contains(strtolower($error), 'incorrect')) {
                    $this->comment("❌ Invalid OTP code");
                    $this->comment("Make sure you entered the correct code from your email");
                } elseif (str_contains(strtolower($error), 'used')) {
                    $this->comment("🚫 This OTP has already been used");
                    $email = $requestData['email'];
                    $this->comment("Request a new one with: php artisan api:test:otp:send --email={$email}");
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
            $this->info("🔑 Authentication successful - token saved!");
        }
    }

    /**
     * Verify database changes
     */
    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $purpose = $requestData['purpose'] ?? 'login';
        
        $this->info("\n📊 Database Changes:");
        $this->line("  - otp_codes: OTP marked as used");
        
        if ($purpose === 'login') {
            $this->line("  - users: Updated last_login_at timestamp");
            $this->line("  - sessions: Created new session");
        }
        
        $this->line("  - activity_logs: OTP verification event logged");
        
        $this->newLine();
        $this->line("🔍 Useful SQL queries:");
        $this->line("  SELECT * FROM otp_codes WHERE email = '{$requestData['email']}' ORDER BY created_at DESC;");
    }

    /**
     * Get example payload for documentation
     */
    public function getExamplePayload(): array
    {
        return [
            'email' => 'user@example.com',
            'otp' => '123456',
            'purpose' => 'login'  // login, verification, password_reset
        ];
    }

    /**
     * Get endpoint description
     */
    public function getEndpointDescription(): string
    {
        return 'Verifies an OTP (One-Time Password) code sent to the user\'s email. ' .
               'The purpose parameter determines what action the OTP is validating (login, email verification, etc.).';
    }
}
