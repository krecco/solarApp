<?php

namespace App\Console\Commands\ApiTest;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

abstract class BaseApiCommand extends Command
{
    /**
     * Base URL for API requests
     */
    protected string $baseUrl;

    /**
     * Path to bearer token file
     */
    protected string $tokenFile;

    /**
     * Path to admin bearer token file
     */
    protected string $adminTokenFile;

    /**
     * Path to responses directory
     */
    protected string $responsesDir;

    /**
     * The HTTP method for this endpoint
     */
    protected string $method = 'GET';

    /**
     * The API endpoint path
     */
    protected string $endpoint = '';

    /**
     * Whether this endpoint requires authentication
     */
    protected bool $requiresAuth = true;

    /**
     * Whether this is an admin endpoint
     */
    protected bool $isAdminEndpoint = false;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->baseUrl = config('app.url', 'http://localhost:8000');
        $this->tokenFile = base_path('cli_tests/bearer.txt');
        $this->adminTokenFile = base_path('cli_tests/admin-bearer.txt');
        $this->responsesDir = base_path('cli_tests/Responses');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("\n🚀 Testing API Endpoint: {$this->method} {$this->endpoint}");
        $this->info(str_repeat('─', 80));

        // Prepare request data
        $data = $this->prepareRequestData();

        // Make the request
        $response = $this->makeRequest($data);

        // Handle the response
        $this->handleResponse($response, $data);

        return 0;
    }

    /**
     * Prepare request data from command options/arguments
     */
    abstract protected function prepareRequestData(): array;

    /**
     * Make the HTTP request
     */
    protected function makeRequest(array $data = []): \Illuminate\Http\Client\Response
    {
        $url = $this->baseUrl . $this->endpoint;
        
        // Build request
        $request = Http::timeout(30)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

        // Add authentication if required
        if ($this->requiresAuth) {
            $token = $this->getToken();
            if ($token) {
                $request = $request->withToken($token);
                $this->info("🔑 Using token: " . substr($token, 0, 20) . "...");
            } else {
                $this->warn("⚠️  No authentication token found. Request may fail.");
            }
        }

        // Display request info
        $this->info("\n📡 Request Details:");
        $this->info("  URL: {$url}");
        $this->info("  Method: {$this->method}");
        
        if (!empty($data)) {
            $this->info("  Payload:");
            $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        // Make the request
        $startTime = microtime(true);
        
        $method = strtolower($this->method);
        $response = match($method) {
            'get' => $request->get($url, $data),
            'post' => $request->post($url, $data),
            'put' => $request->put($url, $data),
            'patch' => $request->patch($url, $data),
            'delete' => $request->delete($url, $data),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$this->method}")
        };
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        $this->info("\n⏱️  Response Time: {$duration}ms");

        return $response;
    }

    /**
     * Handle the API response
     */
    protected function handleResponse(\Illuminate\Http\Client\Response $response, array $requestData): void
    {
        $status = $response->status();
        $statusEmoji = match(true) {
            $status >= 200 && $status < 300 => '✅',
            $status >= 400 && $status < 500 => '⚠️',
            $status >= 500 => '❌',
            default => '❓'
        };

        $this->line("\n{$statusEmoji} Response Status: {$status} " . $this->getStatusText($status));

        // Display headers if interesting
        $this->displayInterestingHeaders($response);

        // Display response body
        if ($response->successful()) {
            $this->info("\n📄 Response Body:");
            $json = $response->json();
            $this->line(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            // Handle token saving for auth endpoints
            $this->handleTokenSaving($json);

            // Verify database changes
            $this->verifyDatabaseChanges($json, $requestData);
        } else {
            $this->error("\n❌ Error Response:");
            $this->displayError($response);
        }

        // Save response to file unless disabled
        if (!$this->option('no-save')) {
            $this->saveResponse($response, $requestData);
        }

        $this->info("\n" . str_repeat('─', 80));
    }

    /**
     * Get authentication token
     */
    protected function getToken(): ?string
    {
        // Check if custom token provided
        if ($this->option('token')) {
            return $this->option('token');
        }

        // Check if we should use a specific token file
        if ($this->option('token-file')) {
            $customFile = base_path('cli_tests/' . $this->option('token-file'));
            if (File::exists($customFile)) {
                return trim(File::get($customFile));
            }
        }

        // Use default token file
        $file = $this->isAdminEndpoint ? $this->adminTokenFile : $this->tokenFile;
        
        if (File::exists($file)) {
            return trim(File::get($file));
        }

        return null;
    }

    /**
     * Save token from response
     */
    protected function handleTokenSaving(array $response): void
    {
        // Override in auth commands
    }

    /**
     * Verify database changes
     */
    protected function verifyDatabaseChanges(array $response, array $requestData): void
    {
        // Override in specific commands
    }

    /**
     * Save response to file
     */
    protected function saveResponse(\Illuminate\Http\Client\Response $response, array $requestData): void
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $endpoint = str_replace(['/', '\\'], '_', trim($this->endpoint, '/'));
        $filename = "{$endpoint}_{$timestamp}.json";
        $filepath = $this->responsesDir . '/' . $filename;

        $data = [
            'timestamp' => Carbon::now()->toIso8601String(),
            'endpoint' => $this->endpoint,
            'method' => $this->method,
            'request' => $requestData,
            'response' => [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json() ?: $response->body(),
            ],
            'duration_ms' => $response->transferStats?->getTransferTime() * 1000,
        ];

        File::ensureDirectoryExists($this->responsesDir);
        File::put($filepath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("💾 Response saved to: cli_tests/Responses/{$filename}");
    }

    /**
     * Display interesting headers
     */
    protected function displayInterestingHeaders(\Illuminate\Http\Client\Response $response): void
    {
        $headers = $response->headers();
        
        if (isset($headers['X-RateLimit-Remaining'])) {
            $this->info("🔄 Rate Limit: {$headers['X-RateLimit-Remaining'][0]} remaining");
        }

        if (isset($headers['X-Request-Id'])) {
            $this->info("🆔 Request ID: {$headers['X-Request-Id'][0]}");
        }
    }

    /**
     * Display error response
     */
    protected function displayError(\Illuminate\Http\Client\Response $response): void
    {
        $body = $response->body();
        
        // Try to parse as JSON
        $decoded = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $this->line(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            
            // Display validation errors specially
            if (isset($decoded['errors']) && is_array($decoded['errors'])) {
                $this->error("\n📋 Validation Errors:");
                foreach ($decoded['errors'] as $field => $errors) {
                    $this->error("  - {$field}: " . implode(', ', (array) $errors));
                }
            }
        } else {
            $this->line($body);
        }
    }

    /**
     * Get status text for HTTP status code
     */
    protected function getStatusText(int $status): string
    {
        return match($status) {
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            default => ''
        };
    }

    /**
     * Add common options to commands
     */
    protected function configureBaseOptions(): void
    {
        $this->addOption('token', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Bearer token to use for authentication');
        $this->addOption('token-file', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Token file to use (relative to cli_tests/)');
        $this->addOption('no-save', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Don\'t save response to file');
    }

    /**
     * Show current database stats
     */
    protected function showDatabaseStats(string $table, array $conditions = []): void
    {
        try {
            $query = DB::table($table);
            
            foreach ($conditions as $field => $value) {
                $query->where($field, $value);
            }
            
            $count = $query->count();
            $latest = $query->latest('id')->first();
            
            $this->info("📊 Database Check - {$table}:");
            $this->info("   Total records: {$count}");
            
            if ($latest) {
                $this->info("   Latest ID: {$latest->id}");
                if (isset($latest->created_at)) {
                    $this->info("   Last created: {$latest->created_at}");
                }
            }
        } catch (\Exception $e) {
            $this->warn("   Could not check {$table}: " . $e->getMessage());
        }
    }

    /**
     * Format JSON data for display
     */
    protected function formatJson($data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Save token to file
     */
    protected function saveToken(string $token, bool $isAdmin = false): void
    {
        $file = $isAdmin ? $this->adminTokenFile : $this->tokenFile;
        File::put($file, $token);
        
        $type = $isAdmin ? 'admin ' : '';
        $this->info("🔑 Saved {$type}token to: " . basename($file));
    }

    /**
     * Clear stored tokens
     */
    public static function clearTokens(): void
    {
        $tokenFile = base_path('cli_tests/bearer.txt');
        $adminTokenFile = base_path('cli_tests/admin-bearer.txt');
        
        if (File::exists($tokenFile)) {
            File::delete($tokenFile);
        }
        
        if (File::exists($adminTokenFile)) {
            File::delete($adminTokenFile);
        }
    }

    /**
     * Get example payload for documentation
     */
    abstract public function getExamplePayload(): array;

    /**
     * Get endpoint description
     */
    abstract public function getEndpointDescription(): string;
}
