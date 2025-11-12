<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Run seeders to ensure roles exist
        $this->seed();

        // Create an admin user
        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->admin->assignRole('admin');
    }

    #[Test]
    public function it_gets_dashboard_statistics()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'users' => [
                        'total',
                        'verified',
                        'created_today',
                        'created_this_week',
                        'created_this_month',
                    ],
                    'roles' => [
                        'admins',
                    ],
                ],
            ]);
    }

    #[Test]
    public function it_lists_users_with_pagination()
    {
        Sanctum::actingAs($this->admin);

        // Create some test users
        User::factory()->count(5)->create()->each(function ($user) {
            $user->assignRole('admin');
        });

        $response = $this->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                        'roles',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function it_shows_a_specific_user()
    {
        Sanctum::actingAs($this->admin);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->assignRole('admin');

        $response = $this->getJson("/api/v1/admin/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ],
            ]);
    }

    #[Test]
    public function it_creates_a_new_user()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'email_verified' => true,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User created successfully',
                'data' => [
                    'name' => 'New User',
                    'email' => 'newuser@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New User',
        ]);
    }

    #[Test]
    public function it_updates_an_existing_user()
    {
        Sanctum::actingAs($this->admin);

        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);
        $user->assignRole('admin');

        $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User updated successfully',
                'data' => [
                    'name' => 'New Name',
                    'email' => 'new@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    #[Test]
    public function it_deletes_a_user()
    {
        Sanctum::actingAs($this->admin);

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->deleteJson("/api/v1/admin/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User deleted successfully',
            ]);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    #[Test]
    public function it_prevents_deleting_self()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/v1/admin/users/{$this->admin->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You cannot delete your own account',
            ]);
    }

    #[Test]
    public function it_denies_access_to_non_admin_users()
    {
        $regularUser = User::factory()->create();
        Sanctum::actingAs($regularUser);

        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(403);
    }
}
