<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\User;
use App\Services\LoggingService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected LoggingService $loggingService;

    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    /**
     * Get the full URL for an avatar
     */
    private function getAvatarUrl(?string $avatarPath): ?string
    {
        if (!$avatarPath) {
            return null;
        }
        return Storage::disk('public')->url($avatarPath);
    }

    /**
     * Get default preferences
     */
    private function getDefaultPreferences(): array
    {
        return [
            'language' => 'en',
            'theme' => 'light',
            'primaryColor' => '#3B82F6',
            'timezone' => 'UTC',
            'notifications' => [
                'email' => true,
                'push' => false,
                'system' => true,
                'subscription' => true,
                'tenant' => true,
            ],
        ];
    }

    /**
     * Get user preferences with defaults
     */
    private function getUserPreferences(User $user): array
    {
        $preferences = $user->preferences ?? $this->getDefaultPreferences();
        return array_merge($this->getDefaultPreferences(), $preferences);
    }

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = DB::transaction(function () use ($validated) {
            // Create the user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Assign default role from config
            $user->assignRole(config('roles.default'));

            // Send verification email
            try {
                event(new Registered($user));
            } catch (\Exception $e) {
                $this->loggingService->warning('Email verification notification failed', $e, [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }

            // Send welcome email
            try {
                $locale = $user->preferences['language'] ?? 'en';
                Mail::to($user->email)->send(new WelcomeEmail($user, $locale));
            } catch (\Exception $e) {
                $this->loggingService->emailError($user->email, 'Welcome Email', $e);
            }

            return $user;
        });

        // Create auth token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $this->getAvatarUrl($user->avatar_url),
                    'email_verified_at' => $user->email_verified_at,
                    'last_login_at' => $user->last_login_at,
                    'role' => $user->getRoleNames()->first() ?: config('roles.default'),
                    'created_at' => $user->created_at,
                ],
                'token' => $token,
                'preferences' => $this->getUserPreferences($user),
            ],
            'meta' => [
                'status' => 'success',
                'message' => 'Registration successful. Please check your email to verify your account.',
            ],
        ], 201);
    }

    /**
     * Login user.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();

        // Update last login timestamp
        $user->update(['last_login_at' => now()]);

        // Revoke all previous tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $this->getAvatarUrl($user->avatar_url),
                    'email_verified_at' => $user->email_verified_at,
                    'last_login_at' => $user->last_login_at,
                    'role' => $user->getRoleNames()->first() ?: config('roles.default'),
                    'created_at' => $user->created_at,
                ],
                'token' => $token,
                'preferences' => $this->getUserPreferences($user),
            ],
            'meta' => [
                'status' => 'success',
                'message' => 'Login successful',
            ],
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $this->getAvatarUrl($user->avatar_url),
                'email_verified_at' => $user->email_verified_at,
                'last_login_at' => $user->last_login_at,
                'role' => $user->getRoleNames()->first() ?: 'user',
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'preferences' => $this->getUserPreferences($user),
            ],
        ]);
    }
}
