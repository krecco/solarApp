<?php

namespace App\Console\Commands\ApiTest;

class ForgotPasswordCommand extends BaseApiCommand
{
    protected $signature = 'api:test:forgot-password
                            {--email= : Email address for password reset}';

    protected $description = 'Request password reset link/token';

    protected string $method = 'POST';
    protected string $endpoint = '/api/v1/password/forgot';
    protected bool $requiresAuth = false;

    public function __construct()
    {
        parent::__construct();
        $this->configureBaseOptions();
    }

    public function handle()
    {
        if (!$this->option('email')) {
            $email = $this->ask('Enter your email address');
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
            'email' => $this->option('email')
        ];
    }

    protected function handleResponse(\Illuminate\Http\Client\Response $response, array $requestData): void
    {
        parent::handleResponse($response, $requestData);

        if ($response->successful()) {
            $this->newLine();
            $this->info("📬 Password reset instructions sent!");
            $this->line("1. Check your email for the reset link/token");
            $this->line("2. Click the link or run:");
            $this->line("   php artisan api:test:reset-password --email={$requestData['email']} --token=YOUR_TOKEN --password=NewPassword123!");
        }
    }

    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $this->info("\n📊 Database Changes:");
        $this->line("  - password_resets: Reset token created");
        $this->line("  - email_logs: Password reset email logged");
    }

    public function getExamplePayload(): array
    {
        return [
            'email' => 'user@example.com'
        ];
    }

    public function getEndpointDescription(): string
    {
        return 'Sends a password reset email with a secure token to reset the user\'s password.';
    }
}
