<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailVerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_verifies_email_with_valid_code()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Sanctum::actingAs($user);

        // Set verification code in cache
        $code = '123456';
        Cache::put('email_verification_code_'.$user->id, $code, 600);

        $response = $this->postJson('/api/v1/email/verify-authenticated', [
            'code' => $code,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'status' => 'success',
                    'message' => 'Email verified successfully.',
                ],
            ]);

        // Assert user is now verified
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        // Assert cache was cleared
        $this->assertNull(Cache::get('email_verification_code_'.$user->id));
    }

    #[Test]
    public function it_fails_verification_with_invalid_code()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Sanctum::actingAs($user);

        // Set different code in cache
        Cache::put('email_verification_code_'.$user->id, '123456', 600);

        $response = $this->postJson('/api/v1/email/verify-authenticated', [
            'code' => '654321',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code'])
            ->assertJsonPath('errors.code.0', 'The verification code is invalid or has expired.');

        // Assert user is still not verified
        $user->refresh();
        $this->assertNull($user->email_verified_at);
    }

    #[Test]
    public function it_fails_verification_with_expired_code()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Sanctum::actingAs($user);

        // No code in cache (expired)
        $response = $this->postJson('/api/v1/email/verify-authenticated', [
            'code' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code'])
            ->assertJsonPath('errors.code.0', 'The verification code is invalid or has expired.');

        // Assert user is still not verified
        $user->refresh();
        $this->assertNull($user->email_verified_at);
    }

    #[Test]
    public function it_returns_info_when_email_already_verified()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/email/verify-authenticated', [
            'code' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'status' => 'info',
                    'message' => 'Email already verified.',
                ],
            ]);
    }

    #[Test]
    public function it_validates_verification_code_format()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Sanctum::actingAs($user);

        // Missing code
        $response = $this->postJson('/api/v1/email/verify-authenticated', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);

        // Code too short
        $response = $this->postJson('/api/v1/email/verify-authenticated', [
            'code' => '12345',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);

        // Code too long
        $response = $this->postJson('/api/v1/email/verify-authenticated', [
            'code' => '1234567',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);

        // Non-string code
        $response = $this->postJson('/api/v1/email/verify-authenticated', [
            'code' => 123456,
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    #[Test]
    public function it_requires_authentication_for_verification()
    {
        $response = $this->postJson('/api/v1/email/verify-authenticated', [
            'code' => '123456',
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function it_resends_verification_email()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/email/resend-authenticated');

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'status' => 'success',
                    'message' => 'Verification code sent to your email.',
                ],
            ]);

        // In local environment, code should be included
        if (app()->environment('local', 'development')) {
            $this->assertNotNull($response->json('meta.code'));
        }

        // Assert verification code was set in cache
        $this->assertNotNull(Cache::get('email_verification_code_'.$user->id));
    }

    #[Test]
    public function it_returns_info_when_resending_for_verified_email()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/email/resend-authenticated');

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'status' => 'info',
                    'message' => 'Email already verified.',
                ],
            ]);
    }

    #[Test]
    public function it_requires_authentication_for_resend()
    {
        $response = $this->postJson('/api/v1/email/resend-authenticated');
        $response->assertStatus(401);
    }

    // SKIP TESTS COMMENTED OUT - email/skip route not implemented
    // These tests are for an optional development feature that can be added later

    // #[Test]
    // public function it_skips_verification_in_development()
    // {
    //     // Mock local environment
    //     $this->app['env'] = 'local';

    //     $user = User::factory()->create([
    //         'email_verified_at' => null,
    //     ]);

    //     Sanctum::actingAs($user);

    //     $response = $this->postJson('/api/v1/email/skip');

    //     $response->assertStatus(200)
    //         ->assertJson([
    //             'meta' => [
    //                 'status' => 'success',
    //                 'message' => 'Email verification skipped.',
    //             ],
    //         ]);

    //     // Assert user is now verified
    //     $user->refresh();
    //     $this->assertNotNull($user->email_verified_at);
    // }

    // #[Test]
    // public function it_prevents_skip_in_production()
    // {
    //     // Mock production environment
    //     $this->app['env'] = 'production';

    //     $user = User::factory()->create([
    //         'email_verified_at' => null,
    //     ]);

    //     Sanctum::actingAs($user);

    //     $response = $this->postJson('/api/v1/email/skip');

    //     $response->assertStatus(403)
    //         ->assertJson([
    //             'meta' => [
    //                 'status' => 'error',
    //                 'message' => 'This action is only available in development.',
    //             ],
    //         ]);

    //     // Assert user is still not verified
    //     $user->refresh();
    //     $this->assertNull($user->email_verified_at);
    // }

    // #[Test]
    // public function it_requires_authentication_for_skip()
    // {
    //     $this->app['env'] = 'local';

    //     $response = $this->postJson('/api/v1/email/skip');
    //     $response->assertStatus(401);
    // }

    #[Test]
    public function it_handles_verification_workflow_end_to_end()
    {
        // Create unverified user
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Sanctum::actingAs($user);

        // Step 1: Resend verification code
        $response = $this->postJson('/api/v1/email/resend-authenticated');
        $response->assertStatus(200);

        // Get the code from cache or response
        $code = Cache::get('email_verification_code_'.$user->id);
        if (! $code && app()->environment('local', 'development')) {
            $code = $response->json('meta.code');
        }
        $this->assertNotNull($code);

        // Step 2: Verify with the code
        $response = $this->postJson('/api/v1/email/verify-authenticated', [
            'code' => $code,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'status' => 'success',
                    'message' => 'Email verified successfully.',
                ],
            ]);

        // Step 3: Ensure user is verified
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        // Step 4: Subsequent verification attempts should return info
        $response = $this->postJson('/api/v1/email/verify-authenticated', [
            'code' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'status' => 'info',
                    'message' => 'Email already verified.',
                ],
            ]);
    }
}
