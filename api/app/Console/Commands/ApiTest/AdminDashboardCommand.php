<?php

namespace App\Console\Commands\ApiTest;

class AdminDashboardCommand extends BaseApiCommand
{
    protected $signature = 'api:test:admin:dashboard
                            {--period= : Time period (today, week, month, year)}
                            {--format=pretty : Output format (pretty, json, table)}';

    protected $description = 'Get admin dashboard statistics and overview';

    protected string $method = 'GET';
    protected string $endpoint = '/api/v1/admin/dashboard';
    protected bool $requiresAuth = true;
    protected bool $isAdminEndpoint = true;

    public function __construct()
    {
        parent::__construct();
        $this->configureBaseOptions();
    }

    protected function prepareRequestData(): array
    {
        $params = [];
        
        if ($period = $this->option('period')) {
            $params['period'] = $period;
        }

        return $params;
    }

    protected function handleResponse(\Illuminate\Http\Client\Response $response, array $requestData): void
    {
        if ($response->successful() && $this->option('format') !== 'json') {
            $data = $response->json()['data'] ?? [];
            $format = $this->option('format');
            
            $this->newLine();
            $this->info("📊 Admin Dashboard" . ($requestData['period'] ?? '' ? ' - ' . ucfirst($requestData['period'] ?? '') : ''));
            $this->line(str_repeat('─', 80));

            if ($format === 'table') {
                $this->displayAsTable($data);
            } else {
                $this->displayAsPretty($data);
            }
            
            $this->line(str_repeat('─', 80));
        } else {
            parent::handleResponse($response, $requestData);
        }
    }

    private function displayAsPretty(array $data): void
    {
        if (isset($data['stats'])) {
            $this->newLine();
            $this->info("📈 Statistics:");
            
            if (isset($data['stats']['users'])) {
                $this->line("  👥 Users:");
                $this->line("     Total: " . number_format($data['stats']['users']['total'] ?? 0));
                $this->line("     Active: " . number_format($data['stats']['users']['active'] ?? 0));
                $this->line("     New (30d): " . number_format($data['stats']['users']['new_last_30_days'] ?? 0));
            }
            
            if (isset($data['stats']['tenants'])) {
                $this->line("\n  🏢 Tenants:");
                $this->line("     Total: " . number_format($data['stats']['tenants']['total'] ?? 0));
                $this->line("     Active: " . number_format($data['stats']['tenants']['active'] ?? 0));
                $this->line("     On trial: " . number_format($data['stats']['tenants']['on_trial'] ?? 0));
            }
            
            if (isset($data['stats']['revenue'])) {
                $this->line("\n  💰 Revenue:");
                $this->line("     MRR: $" . number_format($data['stats']['revenue']['mrr'] ?? 0, 2));
                $this->line("     ARR: $" . number_format($data['stats']['revenue']['arr'] ?? 0, 2));
                $this->line("     This month: $" . number_format($data['stats']['revenue']['current_month'] ?? 0, 2));
            }
        }
        
        if (isset($data['recent_activity'])) {
            $this->newLine();
            $this->info("🕐 Recent Activity:");
            foreach ($data['recent_activity'] as $activity) {
                $this->line("  • " . $activity['description'] . " (" . $activity['time_ago'] . ")");
            }
        }
    }

    private function displayAsTable(array $data): void
    {
        if (isset($data['stats'])) {
            $stats = [];
            
            foreach ($data['stats'] as $category => $values) {
                foreach ($values as $key => $value) {
                    $stats[] = [
                        'Category' => ucfirst($category),
                        'Metric' => ucwords(str_replace('_', ' ', $key)),
                        'Value' => is_numeric($value) ? number_format($value, 2) : $value
                    ];
                }
            }
            
            if (!empty($stats)) {
                $this->newLine();
                $this->table(['Category', 'Metric', 'Value'], $stats);
            }
        }
    }

    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        // Read-only endpoint, no database changes
    }

    public function getExamplePayload(): array
    {
        return [
            'period' => 'month'  // today, week, month, year
        ];
    }

    public function getEndpointDescription(): string
    {
        return 'Provides admin dashboard statistics including user counts, tenant metrics, revenue data, ' .
               'and recent activity. Requires admin role. Supports different time periods and output formats.';
    }
}
