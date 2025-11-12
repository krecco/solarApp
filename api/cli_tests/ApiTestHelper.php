<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

/**
 * API Test Helper for Laravel Tinker
 * 
 * Usage:
 * - In Tinker: require 'cli_tests/ApiTestHelper.php';
 * - Then use: API::get('/api/v1/user');
 *             API::post('/api/v1/login', ['email' => 'test@example.com', 'password' => 'password']);
 */
class ApiTestHelper {
    private static $baseUrl = 'http://localhost:8000';
    private static $tokenFile = __DIR__ . '/bearer.txt';
    private static $lastResponse = null;
    
    /**
     * Make an API request
     */
    public static function request($method, $endpoint, $data = [], $useToken = true) {
        $url = self::$baseUrl . $endpoint;
        $request = Http::baseUrl(self::$baseUrl)
            ->timeout(30)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);
        
        // Add token if exists and requested
        if ($useToken && file_exists(self::$tokenFile)) {
            $token = trim(file_get_contents(self::$tokenFile));
            $request = $request->withToken($token);
            echo "🔑 Using token: " . substr($token, 0, 20) . "...\n";
        }
        
        // Make request
        echo "\n📡 {$method} {$endpoint}\n";
        if (!empty($data)) {
            echo "📤 Payload: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        }
        
        $startTime = microtime(true);
        $response = $request->$method($endpoint, $data);
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        self::$lastResponse = $response;
        
        // Display result
        self::displayResponse($response, $method, $endpoint, $duration);
        
        // Save token if login/register
        if (in_array($endpoint, ['/api/v1/register', '/api/v1/login']) && $response->successful()) {
            $json = $response->json();
            $token = $json['data']['token'] ?? $json['token'] ?? null;
            if ($token) {
                file_put_contents(self::$tokenFile, $token);
                echo "💾 Token saved to bearer.txt\n";
            }
        }
        
        return $response;
    }
    
    /**
     * Display formatted response
     */
    private static function displayResponse($response, $method, $endpoint, $duration) {
        $status = $response->status();
        $statusEmoji = match(true) {
            $status >= 200 && $status < 300 => '✅',
            $status >= 400 && $status < 500 => '⚠️',
            $status >= 500 => '❌',
            default => '❓'
        };
        
        echo "{$statusEmoji} Status: {$status} ({$duration}ms)\n";
        
        // Headers (if interesting)
        $headers = $response->headers();
        if (isset($headers['X-RateLimit-Remaining'])) {
            echo "🔄 Rate Limit: {$headers['X-RateLimit-Remaining'][0]} remaining\n";
        }
        
        // Body
        if ($response->successful()) {
            $json = $response->json();
            echo "📄 Response:\n";
            echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
            
            // Database checks for specific endpoints
            self::checkDatabase($method, $endpoint, $json);
        } else {
            echo "❌ Error Response:\n";
            $body = $response->body();
            
            // Try to parse as JSON for better display
            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
            } else {
                echo $body . "\n";
            }
        }
        
        echo str_repeat('─', 80) . "\n";
    }
    
    /**
     * Check database for specific endpoints
     */
    private static function checkDatabase($method, $endpoint, $response) {
        try {
            if ($endpoint === '/api/v1/register' && isset($response['data']['user'])) {
                $user = DB::table('users')->where('email', $response['data']['user']['email'])->first();
                if ($user) {
                    echo "✅ Database Check:\n";
                    echo "   - User #{$user->id} created\n";
                    echo "   - Email verified: " . ($user->email_verified_at ? 'Yes' : 'No') . "\n";
                    
                    $tenant = DB::table('tenants')->where('id', $user->tenant_id)->first();
                    if ($tenant) {
                        echo "   - Tenant '{$tenant->subdomain}' created (status: {$tenant->status})\n";
                    }
                }
            }
            
            if ($endpoint === '/api/v1/my-tenant' && $method === 'put') {
                echo "✅ Database Updated\n";
            }
        } catch (\Exception $e) {
            echo "⚠️  Database check failed: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Shortcut methods
     */
    public static function get($endpoint, $query = []) { 
        if (!empty($query)) {
            $endpoint .= '?' . http_build_query($query);
        }
        return self::request('get', $endpoint); 
    }
    
    public static function post($endpoint, $data = []) { 
        return self::request('post', $endpoint, $data); 
    }
    
    public static function put($endpoint, $data = []) { 
        return self::request('put', $endpoint, $data); 
    }
    
    public static function patch($endpoint, $data = []) { 
        return self::request('patch', $endpoint, $data); 
    }
    
    public static function delete($endpoint) { 
        return self::request('delete', $endpoint); 
    }
    
    /**
     * Special methods
     */
    public static function noAuth($method, $endpoint, $data = []) {
        return self::request($method, $endpoint, $data, false);
    }
    
    public static function withToken($token) {
        $tempFile = self::$tokenFile . '.temp';
        $originalToken = file_exists(self::$tokenFile) ? file_get_contents(self::$tokenFile) : null;
        
        file_put_contents(self::$tokenFile, $token);
        
        return new class($originalToken, $tempFile) {
            private $originalToken;
            private $tempFile;
            
            public function __construct($originalToken, $tempFile) {
                $this->originalToken = $originalToken;
                $this->tempFile = $tempFile;
            }
            
            public function __call($method, $args) {
                $result = ApiTestHelper::$method(...$args);
                
                // Restore original token
                if ($this->originalToken !== null) {
                    file_put_contents(ApiTestHelper::getTokenFile(), $this->originalToken);
                } else {
                    @unlink(ApiTestHelper::getTokenFile());
                }
                
                return $result;
            }
        };
    }
    
    /**
     * Utility methods
     */
    public static function clearToken() {
        if (file_exists(self::$tokenFile)) {
            unlink(self::$tokenFile);
            echo "🗑️  Token cleared\n";
        }
    }
    
    public static function showToken() {
        if (file_exists(self::$tokenFile)) {
            $token = trim(file_get_contents(self::$tokenFile));
            echo "🔑 Current token: " . substr($token, 0, 50) . "...\n";
        } else {
            echo "❌ No token saved\n";
        }
    }
    
    public static function last() {
        return self::$lastResponse;
    }
    
    public static function lastJson() {
        return self::$lastResponse ? self::$lastResponse->json() : null;
    }
    
    public static function getTokenFile() {
        return self::$tokenFile;
    }
    
    /**
     * Test scenarios
     */
    public static function testAuth($email = null, $password = 'Password123!') {
        $email = $email ?: 'test' . time() . '@example.com';
        
        echo "\n🧪 Testing Authentication Flow\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        
        // Register
        $reg = self::post('/api/v1/register', [
            'name' => 'Test User',
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'company_name' => 'Test Company',
            'subdomain' => 'test-' . time(),
            'plan_id' => 1
        ]);
        
        if (!$reg->successful()) return false;
        
        // Get user
        self::get('/api/v1/user');
        
        // Logout
        self::post('/api/v1/logout');
        
        // Login
        self::post('/api/v1/login', [
            'email' => $email,
            'password' => $password
        ]);
        
        return true;
    }
}

// Create alias for easier access
class_alias('ApiTestHelper', 'API');

// Display welcome message
echo "\n";
echo "🚀 API Test Helper Loaded!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Usage Examples:\n";
echo "  API::get('/api/v1/plans')\n";
echo "  API::post('/api/v1/login', ['email' => 'test@example.com', 'password' => 'password'])\n";
echo "  API::testAuth() // Run complete auth flow test\n";
echo "  API::showToken() // Show current token\n";
echo "  API::clearToken() // Clear saved token\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
