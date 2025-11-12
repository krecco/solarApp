<?php

namespace App\Console\Commands\ApiTest;

class BillingOverviewCommand extends BaseApiCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test:billing:overview';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the billing overview endpoint';

    protected string $method = 'GET';

    protected string $endpoint = '/api/v1/billing/overview';

    protected bool $requiresAuth = true;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->configureBaseOptions();
    }

    /**
     * Prepare request data from command options/arguments
     */
    protected function prepareRequestData(): array
    {
        return [];
    }

    /**
     * Verify database state and display billing information
     */
    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        $this->info("\n📊 Billing Overview:");

        if (isset($response['data'])) {
            $billing = $response['data'];

            // Subscription details
            if (isset($billing['subscription'])) {
                $sub = $billing['subscription'];
                $this->info("\n💳 Current Subscription:");
                $this->info("   - Status: {$sub['status']}");
                $planName = $sub['plan_name'] ?? 'N/A';
                $this->info("   - Plan: {$planName}");
                $interval = $sub['interval'] ?? 'month';
                $this->info("   - Price: \${$sub['amount']}/{$interval}");

                if (isset($sub['current_period_start'])) {
                    $this->info("   - Current Period: {$sub['current_period_start']} to {$sub['current_period_end']}");
                }

                if (isset($sub['trial_end']) && $sub['trial_end']) {
                    $this->info("   - Trial Ends: {$sub['trial_end']}");
                }

                if (isset($sub['cancel_at']) && $sub['cancel_at']) {
                    $this->warn("   - ⚠️ Cancels at: {$sub['cancel_at']}");
                }
            }

            // Payment method
            if (isset($billing['payment_method'])) {
                $pm = $billing['payment_method'];
                $this->info("\n💳 Payment Method:");
                $type = $pm['type'] ?? 'card';
                $this->info("   - Type: {$type}");
                $brand = $pm['brand'] ?? 'N/A';
                $this->info("   - Brand: {$brand}");
                $last4 = $pm['last4'] ?? 'N/A';
                $this->info("   - Last 4: {$last4}");
                $this->info("   - Expires: {$pm['exp_month']}/{$pm['exp_year']}");
            } else {
                $this->warn("\n⚠️ No payment method on file");
            }

            // Invoices
            if (isset($billing['invoices']) && is_array($billing['invoices'])) {
                $this->info("\n📄 Recent Invoices:");
                foreach ($billing['invoices'] as $invoice) {
                    $status = $invoice['status'] ?? 'unknown';
                    $statusEmoji = match ($status) {
                        'paid' => '✅',
                        'open' => '📨',
                        'draft' => '📝',
                        'void' => '🚫',
                        default => '❓'
                    };

                    $invoiceNumber = $invoice['number'] ?? 'N/A';
                    $this->info("   {$statusEmoji} {$invoiceNumber} - \${$invoice['total']} - {$invoice['created']} ({$status})");

                    if (isset($invoice['invoice_pdf'])) {
                        $this->info("      PDF: {$invoice['invoice_pdf']}");
                    }
                }
            }

            // Usage stats
            if (isset($billing['usage'])) {
                $this->info("\n📈 Usage Statistics:");
                foreach ($billing['usage'] as $metric => $value) {
                    $this->info("   - {$metric}: {$value}");
                }
            }

            // Next invoice
            if (isset($billing['upcoming_invoice'])) {
                $upcoming = $billing['upcoming_invoice'];
                $this->info("\n📅 Upcoming Invoice:");
                $this->info("   - Amount: \${$upcoming['total']}");
                $this->info("   - Date: {$upcoming['date']}");
            }

            // Useful SQL
            $this->info("\n🔍 Useful SQL queries:");
            $this->line('   -- Check subscription events');
            $this->line('   SELECT * FROM subscription_events WHERE tenant_id = (SELECT tenant_id FROM users WHERE id = ?) ORDER BY created_at DESC;');
            $this->line('   -- Check Stripe webhook events');
            $this->line('   SELECT * FROM stripe_webhook_events ORDER BY created_at DESC LIMIT 10;');
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
        return 'Get comprehensive billing information including current subscription, payment methods, recent invoices, and usage statistics.';
    }
}
