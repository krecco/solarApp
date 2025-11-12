<?php

namespace App\Console\Commands\ApiTest;

class ResetPasswordCommand extends BaseApiCommand
{
    protected $signature = 'api:test:reset-password
                            {--email= : Email address}
                            {--token= : Reset token from email}
                            {--password= : New password}';

    protected $description = 'Reset password with token';

    protected string $method = 'POST';
    protected string $endpoint = '/api/v1/password/reset';
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
            }
        }

        if (!$this->option('token')) {
            $token = $this->ask('Enter the reset token from your email');
            if ($token) {
                $this->input->setOption('token', $token);
            }
        }

        if (!$this->option('password')) {
            $password = $this->secret('Enter your new password (min 8 characters)');
            if ($password) {
                $this->input->setOption('password', $password);
            }
        }

        if (!$this->option('email') || !$this->option('token') || !$this->option('password')) {
            $this->error("Email, token, and password are required");
            return 1;
        }

        return parent::handle();
    }

    protected function prepareRequestData(): array
    {
        return [
            'email' => $this->option('email'),
            'token' => $this->option('token'),
            'password' => $this->option('password'),
            'password_confirmation' => $this->option('password')
        ];
    }

    protected function handleResponse(\Illuminate\Http\Client\Response $response, array $requestData): void
    {
        parent::handleResponse($response, $requestData);

        if ($response->successful()) {
            $this->newLine();
            $this->info("🎉 Password reset successful!");
            $this->line("You can now login with your new password:");
            $this->line("php artisan api:test:login --email={$requestData['email']}");
        }
    }

    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $this->info("\n📊 Database Changes:");
        $this->line("  - users: Password hash updated");
        $this->line("  - password_resets: Token marked as used/deleted");
    }

    public function getExamplePayload(): array
    {
        return [
            'email' => 'user@example.com',
            'token' => 'reset-token-from-email',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!'
        ];
    }

    public function getEndpointDescription(): string
    {
        return 'Resets a user\'s password using the token received via email from the forgot password request.';
    }
}
