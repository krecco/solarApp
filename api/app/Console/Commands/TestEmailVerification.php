<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class TestEmailVerification extends Command
{
    protected $signature = 'test:email-verification {email?}';
    protected $description = 'Test email verification flow';

    public function handle()
    {
        $email = $this->argument('email') ?? 'test@example.com';
        
        $this->info("Testing Email Verification Flow");
        $this->info("================================");
        
        // Step 1: Create or find user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->info("Creating new user with email: {$email}");
            $user = User::create([
                'name' => 'Test User',
                'email' => $email,
                'password' => Hash::make('password123'),
            ]);
        } else {
            $this->info("Using existing user: {$email}");
        }
        
        // Step 2: Generate verification code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey = 'email_verification_code_' . $user->id;
        
        Cache::put($cacheKey, $code, 3600);
        $this->info("Generated verification code: {$code}");
        $this->info("Cache key: {$cacheKey}");
        
        // Step 3: Test verification
        $this->info("\nTesting verification process...");
        
        // Simulate the public verification endpoint
        $controller = app(\App\Http\Controllers\Api\EmailVerificationController::class);
        $request = request()->merge([
            'email' => $email,
            'code' => $code
        ]);
        
        try {
            $response = $controller->verifyPublic($request);
            $data = json_decode($response->getContent(), true);
            
            $this->info("✓ Verification successful!");
            $this->info("Response status: " . $response->getStatusCode());
            
            if (isset($data['data']['token'])) {
                $this->info("✓ Auth token generated: " . substr($data['data']['token'], 0, 20) . "...");
            }
            
            if (isset($data['data']['user'])) {
                $this->info("✓ User data returned");
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['ID', $data['data']['user']['id']],
                        ['Name', $data['data']['user']['name']],
                        ['Email', $data['data']['user']['email']],
                        ['Verified', $data['data']['user']['email_verified_at'] ? 'Yes' : 'No'],
                        ['Role', $data['data']['user']['role'] ?? 'customer'],
                    ]
                );
            }
            
        } catch (\Exception $e) {
            $this->error("✗ Verification failed: " . $e->getMessage());
        }
        
        // Step 4: Test wrong code
        $this->info("\nTesting with wrong code...");
        
        $wrongRequest = request()->merge([
            'email' => $email,
            'code' => '000000'
        ]);
        
        try {
            $response = $controller->verifyPublic($wrongRequest);
            $this->error("✗ Should have failed but didn't");
        } catch (\Exception $e) {
            $this->info("✓ Correctly rejected wrong code: " . $e->getMessage());
        }
        
        // Step 5: Show current route configuration
        $this->info("\nCurrent Email Verification Routes:");
        $this->info("===================================");
        
        $routes = app('router')->getRoutes();
        foreach ($routes as $route) {
            if (strpos($route->uri(), 'email/verify') !== false || strpos($route->uri(), 'email/resend') !== false) {
                $middleware = $route->middleware() ?? [];
                $hasAuth = in_array('auth:sanctum', $middleware);
                
                $this->info(sprintf(
                    "%-30s %-10s %-50s %s",
                    $route->uri(),
                    implode('|', $route->methods()),
                    $route->getActionName(),
                    $hasAuth ? '[AUTH REQUIRED]' : '[PUBLIC]'
                ));
            }
        }
        
        $this->info("\nTest complete!");
        
        // Cleanup
        Cache::forget($cacheKey);
        
        return Command::SUCCESS;
    }
}
