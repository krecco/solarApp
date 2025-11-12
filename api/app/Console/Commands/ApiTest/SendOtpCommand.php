<?php

namespace App\Console\Commands\ApiTest;

class SendOtpCommand extends BaseApiCommand
{
    protected $signature = 'api:test:otp:send
                            {--email= : Email address to send OTP to}
                            {--purpose=login : Purpose of OTP (login, verification, etc.)}';

    protected $description = 'Send OTP code to email address';

    protected string $method = 'POST';
    protected string $endpoint = '/api/v1/otp/send';
    protected bool $requiresAuth = false;

    public function __construct()
    {
        parent::__construct();
        $this->configureBaseOptions();
    }

    public function handle()
    {
        if (!$this->option('email')) {
            $email = $this->ask('Enter email address to send OTP to');
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
            $this->newLine();
            $this->info("📬 Next Steps:");
            $this->line("1. Check your email for the OTP code");
            $this->line("2. Run: php artisan api:test:otp:verify --email={$requestData['email']} --otp=YOUR_CODE");
        }
    }

    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $this->info("\n📊 Database Changes:");
        $this->line("  - otp_codes: New OTP code created");
        $this->line("  - email_logs: Email send event logged");
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
        return 'Sends a one-time password (OTP) to the specified email address for authentication purposes.';
    }
}
