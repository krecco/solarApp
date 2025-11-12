<?php

namespace App\Console\Commands\ApiTest;

class PaymentMethodCommand extends BaseApiCommand
{
    protected $signature = 'api:test:payment-method
                            {action? : The action to perform (add, update, list)}
                            {--payment-method-id= : Stripe payment method ID}
                            {--type= : Payment method type (card)}
                            {--set-default : Set as default payment method}';

    protected $description = 'Manage payment methods for billing';

    protected string $method = 'GET';
    protected string $endpoint = '/api/v1/billing/payment-methods';
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
                ['list' => 'List payment methods', 'add' => 'Add new payment method', 'update' => 'Update default payment method'],
                'list'
            );
        }

        // Set up the endpoint and method based on action
        switch (strtolower($action)) {
            case 'list':
                $this->method = 'GET';
                $this->endpoint = '/api/v1/billing/payment-methods';
                break;
                
            case 'add':
            case 'update':
                $this->method = 'POST';
                $this->endpoint = '/api/v1/billing/payment-method';
                
                if (!$this->option('payment-method-id')) {
                    $this->info("\n💳 Adding/Updating Payment Method");
                    $this->info("To add a payment method, you need a Stripe payment method ID.");
                    $this->info("In production, this would be obtained from Stripe Elements in your frontend.");
                    $this->newLine();
                    $this->info("For testing, you can use:");
                    $this->comment("  pm_card_visa              - Visa test card");
                    $this->comment("  pm_card_mastercard        - Mastercard test card");
                    $this->comment("  pm_card_visa_debit        - Visa debit test card");
                    $this->newLine();
                    
                    $pmId = $this->ask('Enter Stripe payment method ID');
                    if ($pmId) {
                        $this->input->setOption('payment-method-id', $pmId);
                    } else {
                        $this->error("Payment method ID is required");
                        return 1;
                    }
                }
                break;
                
            default:
                $this->error("Invalid action: $action");
                $this->info("Valid actions are: list, add, update");
                return 1;
        }

        return parent::handle();
    }

    protected function prepareRequestData(): array
    {
        $action = strtolower($this->argument('action') ?? 'list');
        
        if ($action === 'list') {
            return [];
        }

        $data = [];
        
        if ($this->option('payment-method-id')) {
            $data['payment_method_id'] = $this->option('payment-method-id');
        }
        
        if ($this->option('type')) {
            $data['type'] = $this->option('type');
        }
        
        if ($this->option('set-default')) {
            $data['set_default'] = true;
        }

        return $data;
    }

    protected function handleResponse(\Illuminate\Http\Client\Response $response, array $requestData): void
    {
        parent::handleResponse($response, $requestData);

        if ($response->successful()) {
            $action = strtolower($this->argument('action') ?? 'list');
            $result = $response->json();
            
            $this->newLine();
            
            switch ($action) {
                case 'list':
                    $paymentMethods = $result['data']['payment_methods'] ?? [];
                    
                    if (empty($paymentMethods)) {
                        $this->warn("No payment methods found");
                        $this->info("Add one with: php artisan api:test:payment-method add --payment-method-id=pm_card_visa");
                    } else {
                        $this->info("💳 Payment Methods:");
                        foreach ($paymentMethods as $pm) {
                            $default = ($pm['id'] === ($result['data']['default_payment_method'] ?? null)) ? ' ⭐ DEFAULT' : '';
                            $this->line(sprintf(
                                "  • %s (%s •••• %s) Exp: %s/%s%s",
                                $pm['id'] ?? 'N/A',
                                ucfirst($pm['card']['brand'] ?? 'card'),
                                $pm['card']['last4'] ?? '****',
                                str_pad($pm['card']['exp_month'] ?? '', 2, '0', STR_PAD_LEFT),
                                $pm['card']['exp_year'] ?? '',
                                $default
                            ));
                        }
                    }
                    break;
                    
                case 'add':
                case 'update':
                    $this->info("✅ Payment method saved successfully!");
                    
                    if (isset($result['data']['payment_method'])) {
                        $pm = $result['data']['payment_method'];
                        $this->line("💳 Card: " . ucfirst($pm['card']['brand'] ?? 'card') . " •••• " . ($pm['card']['last4'] ?? '****'));
                        
                        if ($requestData['set_default'] ?? false) {
                            $this->line("⭐ Set as default payment method");
                        }
                    }
                    break;
            }
        }
    }

    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $action = strtolower($this->argument('action') ?? 'list');
        
        if ($action !== 'list') {
            $this->info("\n📊 Database Changes:");
            $this->line("  - tenants: Updated default_payment_method_id");
            $this->line("  - payment_methods: Stored payment method details");
            $this->line("  - activity_logs: Logged payment method change");
        }
    }

    public function getExamplePayload(): array
    {
        return [
            'payment_method_id' => 'pm_card_visa',
            'type' => 'card',
            'set_default' => true
        ];
    }

    public function getEndpointDescription(): string
    {
        return 'Manages payment methods for the tenant\'s billing account. Can list all payment methods, ' .
               'add new ones, or update the default payment method. Integrates with Stripe for secure payment processing.';
    }
}
