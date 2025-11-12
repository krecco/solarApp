<?php

namespace App\Console\Commands\ApiTest;

class ResendOtpCommand extends BaseApiCommand
{
    protected $signature = 'api:test:otp:resend
                            {--email= : Email address to resend OTP to}
                            {--purpose=login : Purpose of OTP}';

    protected $description = 'Resend OTP code to email address';

    protected string $method = 'POST';
    protected string $endpoint = '/api/v1/otp/resend';
    protected bool $requiresAuth = false;

    public function __construct()
    {
        parent::__construct();
        $this->configureBaseOptions();
    }

    public function handle()
    {
        if (!$this->option('email')) {
            $email = $this->ask('Enter email address to resend OTP to');
            if ($email) {
                $this->input->setOption('email', $email);
            } else {
                $this->error("Email is required");
                return 1;
            }
        }

        return parent::handle();
    }

    protected function prepareRequestData(): array
    {
        return [
            'email' => $this->option('email'),
            'purpose' => $this->option('purpose') ?? 'login'
        ];
    }

    protected function handleResponse(\Illuminate\Http\Client\Response $response, array $requestData): void
    {
        parent::handleResponse($response, $requestData);

        if ($response->successful()) {
            $result = $response->json();
            
            if (isset($result['data']['throttle_seconds'])) {
                $this->line("⏳ Next resend available in: " . $result['data']['throttle_seconds'] . " seconds");
            }
            
            $this->newLine();
            $this->info("📬 Check your email for the new OTP code");
        }
    }

    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $this->info("\n📊 Database Changes:");
        $this->line("  - otp_codes: Previous OTP invalidated, new OTP created");
        $this->line("  - rate_limits: Throttle counter updated");
    }

    public function getExamplePayload(): array
    {
        return [
            'email' => 'user@example.com',
            'purpose' => 'login'
        ];
    }

    public function getEndpointDescription(): string
    {
        return 'Resends a new OTP code to the specified email, invalidating any previous codes.';
    }
}
