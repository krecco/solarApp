<?php

namespace App\Console\Commands\ApiTest;

class TestCommand extends BaseApiCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test 
        {endpoint : The API endpoint to test (e.g., /api/v1/user)}
        {--method=GET : HTTP method (GET, POST, PUT, PATCH, DELETE)}
        {--data= : JSON data for request body}
        {--auth=true : Whether to include authentication token (true/false)}
        {--admin : Use admin token instead of regular token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test any API endpoint with custom parameters';

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
        // Set properties from arguments/options
        $this->endpoint = $this->argument('endpoint');
        $this->method = strtoupper($this->option('method'));
        $this->requiresAuth = filter_var($this->option('auth'), FILTER_VALIDATE_BOOLEAN);
        $this->isAdminEndpoint = $this->option('admin');

        // Validate method
        $validMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        if (!in_array($this->method, $validMethods)) {
            $this->error("Invalid method: {$this->method}. Valid methods: " . implode(', ', $validMethods));
            return 1;
        }

        return parent::handle();
    }

    /**
     * Prepare request data from command options/arguments
     */
    protected function prepareRequestData(): array
    {
        if (!$this->option('data')) {
            return [];
        }

        $data = json_decode($this->option('data'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON data provided: ' . json_last_error_msg());
            $this->info("\nExample usage:");
            $this->line('  php artisan api:test /api/v1/my-tenant --method=PUT --data=\'{"company_name":"New Name"}\'');
            exit(1);
        }

        return $data;
    }

    /**
     * Get example payload for documentation
     */
    public function getExamplePayload(): array
    {
        return [
            'example' => 'This is a generic test command',
            'usage' => 'Provide actual data via --data option',
        ];
    }

    /**
     * Get endpoint description
     */
    public function getEndpointDescription(): string
    {
        return 'Generic API testing command for any endpoint. Useful for testing new or undocumented endpoints.';
    }
}
