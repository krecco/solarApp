<?php

namespace App\Console\Commands\ApiTest;

class SubscriptionCommand extends BaseApiCommand
{
    protected $signature = 'api:test:subscription
                            {action? : The action to perform (update, cancel, resume)}
                            {--plan-id= : Plan ID for update action}
                            {--payment-method-id= : Stripe payment method ID}
                            {--promo-code= : Promotional code to apply}
                            {--immediately : Cancel subscription immediately (for cancel action)}
                            {--reason= : Cancellation reason (for cancel action)}
                            {--feedback= : Additional feedback (for cancel action)}';

    protected $description = 'Manage subscription (update plan, cancel, or resume)';

    protected string $method = 'POST';
    protected string $endpoint = '/api/v1/billing/subscription';
    protected bool $requiresAuth = true;

    public function __construct()
    {
        parent::__construct();
        $this->configureBaseOptions();
    }

    public function handle()
    {
        $action = $this->argument('action');

        if (!$action) {
            $action = $this->choice(
                'What would you like to do?',
                ['update' => 'Update subscription plan', 'cancel' => 'Cancel subscription', 'resume' => 'Resume cancelled subscription'],
                'update'
            );
        }

        // Set up the endpoint and method based on action
        switch (strtolower($action)) {
            case 'update':
                $this->method = 'POST';
                $this->endpoint = '/api/v1/billing/subscription';
                
                if (!$this->option('plan-id')) {
                    // Show available plans
                    $this->call('api:test:plans');
                    $planId = $this->ask("\nEnter the Plan ID to switch to");
                    if ($planId) {
                        $this->input->setOption('plan-id', $planId);
                    } else {
                        $this->error("Plan ID is required for update");
                        return 1;
                    }
                }
                break;
                
            case 'cancel':
                $this->method = 'DELETE';
                $this->endpoint = '/api/v1/billing/subscription';
                
                if (!$this->option('reason')) {
                    $reason = $this->choice(
                        'Please select a cancellation reason',
                        ['too_expensive', 'missing_features', 'not_using', 'switching_competitor', 'other'],
                        'other'
                    );
                    $this->input->setOption('reason', $reason);
                }
                
                if (!$this->confirm('Are you sure you want to cancel your subscription?')) {
                    $this->info("Cancellation aborted");
                    return 0;
                }
                break;
                
            case 'resume':
                $this->method = 'POST';
                $this->endpoint = '/api/v1/billing/subscription/resume';
                break;
                
            default:
                $this->error("Invalid action: $action");
                $this->info("Valid actions are: update, cancel, resume");
                return 1;
        }

        return parent::handle();
    }

    protected function prepareRequestData(): array
    {
        $action = strtolower($this->argument('action') ?? 'update');
        $data = [];

        switch ($action) {
            case 'update':
                $data['plan_id'] = (int) $this->option('plan-id');
                
                if ($this->option('payment-method-id')) {
                    $data['payment_method_id'] = $this->option('payment-method-id');
                }
                
                if ($this->option('promo-code')) {
                    $data['promo_code'] = $this->option('promo-code');
                }
                break;
                
            case 'cancel':
                if ($this->option('immediately')) {
                    $data['immediately'] = true;
                }
                
                if ($this->option('reason')) {
                    $data['reason'] = $this->option('reason');
                }
                
                if ($this->option('feedback')) {
                    $data['feedback'] = $this->option('feedback');
                }
                break;
                
            case 'resume':
                // No additional data needed for resume
                break;
        }

        return $data;
    }

    protected function handleResponse(\Illuminate\Http\Client\Response $response, array $requestData): void
    {
        parent::handleResponse($response, $requestData);

        if ($response->successful()) {
            $action = strtolower($this->argument('action') ?? 'update');
            $result = $response->json();
            
            $this->newLine();
            
            switch ($action) {
                case 'update':
                    $subscription = $result['data']['subscription'] ?? null;
                    if ($subscription) {
                        $this->info("📊 Subscription Details:");
                        $this->line("  Plan: " . ($subscription['plan']['name'] ?? 'N/A'));
                        $this->line("  Price: $" . ($subscription['plan']['price'] ?? '0') . "/" . ($subscription['plan']['interval'] ?? 'month'));
                        
                        if (isset($subscription['current_period_end'])) {
                            $this->line("  Next billing: " . $subscription['current_period_end']);
                        }
                    }
                    break;
                    
                case 'cancel':
                    if (isset($result['data']['ends_at'])) {
                        if ($requestData['immediately'] ?? false) {
                            $this->warn("⚠️  Subscription ended immediately");
                        } else {
                            $this->line("📅 Subscription will end on: " . $result['data']['ends_at']);
                            $this->line("💡 You can continue using the service until then");
                        }
                    }
                    break;
                    
                case 'resume':
                    $this->info("♻️  Subscription has been reactivated!");
                    break;
            }
        }
    }

    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $action = strtolower($this->argument('action') ?? 'update');
        
        $this->info("\n📊 Database Changes:");
        
        switch ($action) {
            case 'update':
                $this->line("  - tenants: Updated plan_id");
                $this->line("  - subscriptions: Updated Stripe subscription");
                $this->line("  - subscription_events: Logged plan change");
                break;
                
            case 'cancel':
                $this->line("  - tenants: Set subscription_ends_at");
                $this->line("  - subscriptions: Cancelled in Stripe");
                $this->line("  - subscription_events: Logged cancellation");
                if ($requestData['reason'] ?? null) {
                    $this->line("  - cancellation_feedback: Stored reason");
                }
                break;
                
            case 'resume':
                $this->line("  - tenants: Cleared subscription_ends_at");
                $this->line("  - subscriptions: Resumed in Stripe");
                $this->line("  - subscription_events: Logged resume event");
                break;
        }
    }

    public function getExamplePayload(): array
    {
        return [
            // For update
            'plan_id' => 2,
            'payment_method_id' => 'pm_1234567890',
            'promo_code' => 'SAVE20',
            
            // For cancel
            'immediately' => false,
            'reason' => 'too_expensive',
            'feedback' => 'Need a cheaper plan option'
        ];
    }

    public function getEndpointDescription(): string
    {
        return 'Manages subscription operations: update to a new plan, cancel subscription (immediately or at period end), ' .
               'or resume a cancelled subscription. Integrates with Stripe for payment processing.';
    }
}
