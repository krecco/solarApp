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
