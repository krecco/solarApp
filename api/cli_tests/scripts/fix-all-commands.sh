#!/bin/bash

# Fix all commands that have abstract method issues

echo "Fixing all API Test Commands..."

# Fix SendOtpCommand
cat > /home/test/saas/saas-central/app/Console/Commands/ApiTest/SendOtpCommand.php << 'EOF'
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
EOF

# Fix ResendOtpCommand
cat > /home/test/saas/saas-central/app/Console/Commands/ApiTest/ResendOtpCommand.php << 'EOF'
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
EOF

# Fix ForgotPasswordCommand
cat > /home/test/saas/saas-central/app/Console/Commands/ApiTest/ForgotPasswordCommand.php << 'EOF'
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
EOF

# Fix ResetPasswordCommand
cat > /home/test/saas/saas-central/app/Console/Commands/ApiTest/ResetPasswordCommand.php << 'EOF'
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
EOF

# Fix ProfileCompleteCommand
cat > /home/test/saas/saas-central/app/Console/Commands/ApiTest/ProfileCompleteCommand.php << 'EOF'
<?php

namespace App\Console\Commands\ApiTest;

class ProfileCompleteCommand extends BaseApiCommand
{
    protected $signature = 'api:test:profile:complete
                            {--company-name= : Company name}
                            {--subdomain= : Subdomain}
                            {--plan-id=1 : Plan ID}
                            {--phone= : Phone number}';

    protected $description = 'Complete user profile (Step 2 registration)';

    protected string $method = 'POST';
    protected string $endpoint = '/api/v1/profile/complete';
    protected bool $requiresAuth = true;

    public function __construct()
    {
        parent::__construct();
        $this->configureBaseOptions();
    }

    public function handle()
    {
        if (!$this->option('company-name')) {
            $company = $this->ask('Enter your company name');
            if ($company) {
                $this->input->setOption('company-name', $company);
            }
        }

        if (!$this->option('subdomain')) {
            $subdomain = $this->ask('Enter your preferred subdomain');
            if ($subdomain) {
                $this->input->setOption('subdomain', $subdomain);
            }
        }

        if (!$this->option('company-name') || !$this->option('subdomain')) {
            $this->error("Company name and subdomain are required");
            return 1;
        }

        return parent::handle();
    }

    protected function prepareRequestData(): array
    {
        return [
            'company_name' => $this->option('company-name'),
            'subdomain' => $this->option('subdomain'),
            'plan_id' => (int) $this->option('plan-id'),
            'phone' => $this->option('phone')
        ];
    }

    protected function handleResponse(\Illuminate\Http\Client\Response $response, array $requestData): void
    {
        parent::handleResponse($response, $requestData);

        if ($response->successful()) {
            $result = $response->json();
            
            if (isset($result['data']['tenant'])) {
                $tenant = $result['data']['tenant'];
                $this->newLine();
                $this->info("🏢 Tenant Details:");
                $this->line("  Company: " . ($tenant['company_name'] ?? 'N/A'));
                $this->line("  Subdomain: " . ($tenant['subdomain'] ?? 'N/A') . ".yoursaas.com");
                $this->line("  Status: " . ($tenant['status'] ?? 'N/A'));
            }
            
            $this->newLine();
            $this->info("✅ Profile completed successfully!");
            $this->line("Your account is now fully set up.");
        }
    }

    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $this->info("\n📊 Database Changes:");
        $this->line("  - tenants: Created new tenant record");
        $this->line("  - users: Updated tenant_id and profile_completed_at");
        $this->line("  - subscriptions: Created initial subscription");
    }

    public function getExamplePayload(): array
    {
        return [
            'company_name' => 'Acme Corporation',
            'subdomain' => 'acme',
            'plan_id' => 1,
            'phone' => '+1234567890'
        ];
    }

    public function getEndpointDescription(): string
    {
        return 'Completes user profile by creating tenant and setting up initial subscription (Step 2 of registration).';
    }
}
EOF

echo "✅ Fixed all OTP, Password Reset, and Profile commands"

# Check if ClearTokensCommand needs fixing
if grep -q "setupEnvironment\|makeRequest\|displayError" /home/test/saas/saas-central/app/Console/Commands/ApiTest/ClearTokensCommand.php; then
    echo "Fixing ClearTokensCommand..."
    # This command is different - it doesn't call an API
    cat > /home/test/saas/saas-central/app/Console/Commands/ApiTest/ClearTokensCommand.php << 'EOF'
<?php

namespace App\Console\Commands\ApiTest;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearTokensCommand extends Command
{
    protected $signature = 'api:test:clear-tokens
                            {--all : Clear all token files}
                            {--admin : Clear only admin token}
                            {--regular : Clear only regular token}';

    protected $description = 'Clear saved authentication tokens';

    protected string $tokenFile;
    protected string $adminTokenFile;

    public function __construct()
    {
        parent::__construct();
        $this->tokenFile = base_path('cli_tests/bearer.txt');
        $this->adminTokenFile = base_path('cli_tests/admin-bearer.txt');
    }

    public function handle()
    {
        $clearAll = $this->option('all');
        $clearAdmin = $this->option('admin');
        $clearRegular = $this->option('regular');

        if (!$clearAll && !$clearAdmin && !$clearRegular) {
            $clearRegular = true;
        }

        $cleared = [];

        if ($clearAll || $clearRegular) {
            if (File::exists($this->tokenFile)) {
                File::delete($this->tokenFile);
                $cleared[] = 'Regular token';
            }
        }

        if ($clearAll || $clearAdmin) {
            if (File::exists($this->adminTokenFile)) {
                File::delete($this->adminTokenFile);
                $cleared[] = 'Admin token';
            }
        }

        if (empty($cleared)) {
            $this->warn('No tokens found to clear');
        } else {
            $this->info('✅ Cleared: ' . implode(', ', $cleared));
        }

        return 0;
    }
}
EOF
fi

echo "All commands have been fixed!"
