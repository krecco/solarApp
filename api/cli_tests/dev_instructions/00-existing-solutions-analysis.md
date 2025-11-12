# Laravel API CLI Testing - Research Summary & Recommendations

## Existing Laravel CLI Testing Solutions (From Web Search)

After searching for Laravel CLI API testing tools, here's what I found:

### 1. **No Direct "Swagger CLI" for Laravel**
There's no Laravel package that provides a Swagger-like CLI testing interface out of the box. However, there are several alternatives:

### 2. **Newman (Postman's CLI Tool)** ✅ RECOMMENDED
- **What**: CLI runner for Postman collections
- **Install**: `npm install -g newman`
- **Laravel Integration**: Use `andreaselia/laravel-api-to-postman` package
```bash
composer require andreaselia/laravel-api-to-postman --dev
php artisan export:postman --bearer="your-token"
newman run storage/app/[collection].postman_collection.json
```

### 3. **Laravel Artisan Tinker** (Your Suggestion) ✅ GOOD OPTION
You can use Laravel Boost Tinker for interactive API testing:
```php
// In tinker
$response = Http::post('http://localhost:8000/api/v1/register', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'Password123!',
    'password_confirmation' => 'Password123!',
    'company_name' => 'Acme Corp',
    'subdomain' => 'acme'
]);

// Save token
file_put_contents('bearer.txt', $response->json()['data']['token']);

// Use token for next request
$token = trim(file_get_contents('bearer.txt'));
$user = Http::withToken($token)->get('http://localhost:8000/api/v1/user');
```

### 4. **Pest/PHPUnit with Custom Helpers**
Create test files that can be run individually:
```bash
pest tests/Api/RegisterTest.php --filter="can register"
```

## My Recommendation: Custom Artisan Commands with Tinker

Since you specifically want to use Laravel Boost Tinker and test your existing routes, here's a practical approach:

### Create a CLI Testing System Using Tinker

```php
// cli_tests/test-register.php
<?php
use Illuminate\Support\Facades\Http;

$baseUrl = 'http://localhost:8000';

// Test Registration
$response = Http::post("{$baseUrl}/api/v1/register", [
    'name' => 'Test User',
    'email' => 'test' . time() . '@example.com',
    'password' => 'Password123!',
    'password_confirmation' => 'Password123!',
    'company_name' => 'Test Company',
    'subdomain' => 'test-' . time(),
    'plan_id' => 1
]);

if ($response->successful()) {
    echo "✅ Registration successful!\n";
    echo "Token: " . $response->json()['data']['token'] . "\n";
    
    // Save token
    file_put_contents(__DIR__ . '/bearer.txt', $response->json()['data']['token']);
    
    // Check database
    $user = \App\Models\User::where('email', $response->json()['data']['user']['email'])->first();
    echo "User created: ID #{$user->id}\n";
    echo "Tenant created: {$user->tenant->subdomain}\n";
} else {
    echo "❌ Registration failed!\n";
    echo "Status: " . $response->status() . "\n";
    echo "Response: " . $response->body() . "\n";
}
```

### Run with Tinker:
```bash
php artisan tinker < cli_tests/test-register.php
```

## Complete Testing Setup for Your Routes

Based on your routes, here's a complete testing structure:

### 1. Authentication Tests
```php
// cli_tests/auth/register.php
// cli_tests/auth/login.php
// cli_tests/auth/logout.php
// cli_tests/auth/verify-email.php
// cli_tests/auth/forgot-password.php
```

### 2. Tenant Management Tests
```php
// cli_tests/tenant/get-my-tenant.php
// cli_tests/tenant/update-my-tenant.php
// cli_tests/tenant/deployment-info.php
```

### 3. Billing Tests
```php
// cli_tests/billing/overview.php
// cli_tests/billing/subscription.php
// cli_tests/billing/payment-method.php
```

### 4. Admin Tests
```php
// cli_tests/admin/dashboard.php
// cli_tests/admin/users.php
// cli_tests/admin/tenants.php
// cli_tests/admin/plans.php
```

## Example Complete Test Script

```php
// cli_tests/run-auth-flow.php
<?php
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ApiTester {
    private $baseUrl;
    private $token;
    
    public function __construct($baseUrl = 'http://localhost:8000') {
        $this->baseUrl = $baseUrl;
    }
    
    public function register($data) {
        echo "\n📝 Testing Registration...\n";
        
        $response = Http::post("{$this->baseUrl}/api/v1/register", $data);
        
        if ($response->successful()) {
            echo "✅ Registration successful!\n";
            $this->token = $response->json()['data']['token'] ?? null;
            
            if ($this->token) {
                file_put_contents(__DIR__ . '/bearer.txt', $this->token);
                echo "🔑 Token saved to bearer.txt\n";
            }
            
            // Database verification
            $user = DB::table('users')->where('email', $data['email'])->first();
            $tenant = DB::table('tenants')->where('id', $user->tenant_id)->first();
            
            echo "👤 User created: ID #{$user->id}\n";
            echo "🏢 Tenant created: {$tenant->subdomain}.yoursaas.com\n";
            
            return $response->json();
        } else {
            echo "❌ Registration failed!\n";
            echo "Status: {$response->status()}\n";
            echo "Error: " . json_encode($response->json(), JSON_PRETTY_PRINT) . "\n";
            return null;
        }
    }
    
    public function login($email, $password) {
        echo "\n🔐 Testing Login...\n";
        
        $response = Http::post("{$this->baseUrl}/api/v1/login", [
            'email' => $email,
            'password' => $password
        ]);
        
        if ($response->successful()) {
            echo "✅ Login successful!\n";
            $this->token = $response->json()['data']['token'];
            file_put_contents(__DIR__ . '/bearer.txt', $this->token);
            return $response->json();
        } else {
            echo "❌ Login failed!\n";
            return null;
        }
    }
    
    public function getUser() {
        echo "\n👤 Getting Current User...\n";
        
        if (!$this->token && file_exists(__DIR__ . '/bearer.txt')) {
            $this->token = trim(file_get_contents(__DIR__ . '/bearer.txt'));
        }
        
        $response = Http::withToken($this->token)->get("{$this->baseUrl}/api/v1/user");
        
        if ($response->successful()) {
            echo "✅ User retrieved!\n";
            $user = $response->json()['data'];
            echo "Name: {$user['name']}\n";
            echo "Email: {$user['email']}\n";
            echo "Role: {$user['role']}\n";
            return $user;
        } else {
            echo "❌ Failed to get user!\n";
            return null;
        }
    }
}

// Run tests
$tester = new ApiTester();

// Test registration
$email = 'test' . time() . '@example.com';
$tester->register([
    'name' => 'Test User',
    'email' => $email,
    'password' => 'Password123!',
    'password_confirmation' => 'Password123!',
    'company_name' => 'Test Company',
    'subdomain' => 'test-' . time(),
    'plan_id' => 1
]);

// Test get user
$tester->getUser();

// Test login
$tester->login($email, 'Password123!');
```

### Run it:
```bash
php artisan tinker < cli_tests/run-auth-flow.php
```

## Better Alternative: Custom Artisan Command

Create a single command for all tests:

```php
// app/Console/Commands/ApiTest.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ApiTest extends Command
{
    protected $signature = 'api:test {endpoint} {--method=GET} {--data=} {--token=}';
    protected $description = 'Test API endpoints';
    
    private $baseUrl;
    
    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = config('app.url');
    }
    
    public function handle()
    {
        $endpoint = $this->argument('endpoint');
        $method = $this->option('method');
        $data = $this->option('data') ? json_decode($this->option('data'), true) : [];
        $token = $this->option('token') ?: $this->getStoredToken();
        
        // Build request
        $request = Http::baseUrl($this->baseUrl);
        
        if ($token) {
            $request = $request->withToken($token);
        }
        
        // Make request
        $response = $request->$method("/api/v1/{$endpoint}", $data);
        
        // Display results
        $this->displayResponse($response);
        
        // Save token if present
        if ($endpoint === 'register' || $endpoint === 'login') {
            if ($response->successful() && isset($response->json()['data']['token'])) {
                $this->saveToken($response->json()['data']['token']);
            }
        }
    }
    
    private function displayResponse($response)
    {
        $status = $response->status();
        $emoji = $response->successful() ? '✅' : '❌';
        
        $this->info("{$emoji} Status: {$status}");
        
        if ($response->successful()) {
            $this->info("Response:");
            $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));
        } else {
            $this->error("Error Response:");
            $this->line($response->body());
        }
    }
    
    private function getStoredToken()
    {
        $tokenFile = storage_path('app/cli_tests/bearer.txt');
        return file_exists($tokenFile) ? trim(file_get_contents($tokenFile)) : null;
    }
    
    private function saveToken($token)
    {
        $tokenFile = storage_path('app/cli_tests/bearer.txt');
        file_put_contents($tokenFile, $token);
        $this->info("🔑 Token saved to {$tokenFile}");
    }
}
```

Usage:
```bash
# Register
php artisan api:test register --method=POST --data='{"name":"John Doe","email":"john@example.com","password":"Password123!","password_confirmation":"Password123!","company_name":"Acme","subdomain":"acme"}'

# Get user (uses saved token)
php artisan api:test user

# Update tenant
php artisan api:test my-tenant --method=PUT --data='{"company_name":"Acme Corporation"}'
```

## Summary

1. **No "Swagger CLI" exists** for Laravel specifically
2. **Best Options**:
   - Use Laravel Boost Tinker with custom scripts (as shown above)
   - Create a custom Artisan command
   - Use Newman with Postman collections
3. **For your needs**, I recommend the Tinker approach with organized test scripts since you already have Laravel Boost

The scripts above will let you:
- Test individual endpoints
- Save/load bearer tokens automatically
- Verify database changes
- See formatted output
- Chain multiple requests together

All without building anything from scratch!
