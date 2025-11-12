<?php

namespace App\Console\Commands\ApiTest;

class PlansCommand extends BaseApiCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test:plans 
        {--id= : Get a specific plan by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the plans endpoints (list all or get specific plan)';

    protected string $method = 'GET';
    protected string $endpoint = '/api/v1/plans';
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
        // Modify endpoint if specific plan requested
        if ($planId = $this->option('id')) {
            $this->endpoint = "/api/v1/plans/{$planId}";
        }

        return parent::handle();
    }

    /**
     * Prepare request data from command options/arguments
     */
    protected function prepareRequestData(): array
    {
        // No request data needed for GET
        return [];
    }

    /**
     * Verify database state and display plan information
     */
    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $this->info("\n📊 Plans Information:");

        if ($this->option('id')) {
            // Single plan
            if (isset($response['data'])) {
                $this->displayPlanDetails($response['data']);
            }
        } else {
            // Multiple plans
            if (isset($response['data']) && is_array($response['data'])) {
                $this->info("Found " . count($response['data']) . " plans:");
                
                foreach ($response['data'] as $plan) {
                    $this->info("\n" . str_repeat('-', 40));
                    $this->displayPlanDetails($plan);
                }
                
                // Show database stats
                $this->info("\n" . str_repeat('-', 40));
                $this->showDatabaseStats('plans');
            }
        }

        $this->info("\n🔍 Useful SQL queries:");
        $this->line("   -- Get all active plans");
        $this->line("   SELECT * FROM plans WHERE active = true ORDER BY price;");
        $this->line("   -- Count tenants per plan");
        $this->line("   SELECT p.name, COUNT(t.id) as tenant_count FROM plans p LEFT JOIN tenants t ON p.id = t.plan_id GROUP BY p.id;");
    }

    /**
     * Display plan details
     */
    private function displayPlanDetails(array $plan): void
    {
        $this->info("📦 Plan: {$plan['name']}");
        $this->info("   - ID: {$plan['id']}");
        $this->info("   - Price: \${$plan['price']}/{$plan['billing_period']}");
        $this->info("   - Active: " . ($plan['active'] ? 'Yes' : 'No'));
        
        if (isset($plan['features']) && is_array($plan['features'])) {
            $this->info("   - Features:");
            foreach ($plan['features'] as $feature => $value) {
                if (is_bool($value)) {
                    $value = $value ? '✓' : '✗';
                }
                $this->info("     • {$feature}: {$value}");
            }
        }
        
        if (isset($plan['stripe_price_id'])) {
            $this->info("   - Stripe Price ID: {$plan['stripe_price_id']}");
        }
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
        return 'Get all available subscription plans or a specific plan by ID. This endpoint is public and does not require authentication.';
    }
}
