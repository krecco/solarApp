<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    /**
     * Verify email using the verification code.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'meta' => [
                    'status' => 'info',
                    'message' => 'Email already verified.',
                ],
            ]);
        }

        // Get the stored verification code
        $storedCode = cache()->get('email_verification_code_' . $user->id);

        if (!$storedCode || $storedCode !== $request->code) {
            throw ValidationException::withMessages([
                'code' => ['The verification code is invalid or has expired.'],
            ]);
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        // Clear the verification code from cache
        cache()->forget('email_verification_code_' . $user->id);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Email verified successfully.',
            ],
        ]);
    }

    /**
     * Resend verification email.
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'meta' => [
                    'status' => 'info',
                    'message' => 'Email already verified.',
                ],
            ]);
        }

        // Generate verification code before sending notification
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store the code in cache for 60 minutes
        cache()->put('email_verification_code_' . $user->id, $code, 3600);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Verification code sent to your email.',
                // Include code in development for testing
                'code' => app()->environment('local', 'development') ? $code : null,
            ],
        ]);
    }

    /**
     * Skip verification (for testing purposes).
     */
    public function skip(Request $request): JsonResponse
    {
        if (!app()->environment('local', 'development')) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'This action is only available in development.',
                ],
            ], 403);
        }

        $user = $request->user();
        $user->markEmailAsVerified();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Email verification skipped.',
            ],
        ]);
    }

    /**
     * Verify email using code (PUBLIC - no auth required).
     */
    public function verifyPublic(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            // Create token for verified user to log them in
            $token = $user->createToken('auth-token')->plainTextToken;
            
            return response()->json([
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,
                        'role' => $user->getRoleNames()->first() ?: 'customer',
                        'profile_completed' => $user->profile_status === 'completed',
                        'needs_profile_completion' => $user->needsProfileCompletion(),
                        'tenant' => $user->tenant ? [
                            'id' => $user->tenant->id,
                            'uuid' => $user->tenant->uuid,
                            'subdomain' => $user->tenant->subdomain,
                            'company_name' => $user->tenant->company_name,
                            'plan' => $user->tenant->plan?->name ?: 'Free',
                        ] : null,
                    ],
                    'token' => $token,
                ],
                'meta' => [
                    'status' => 'info',
                    'message' => 'Email already verified. You are now logged in.',
                ],
            ]);
        }

        // Get the stored verification code
        $storedCode = cache()->get('email_verification_code_' . $user->id);

        if (!$storedCode || $storedCode !== $request->code) {
            throw ValidationException::withMessages([
                'code' => ['The verification code is invalid or has expired.'],
            ]);
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        // Clear the verification code from cache
        cache()->forget('email_verification_code_' . $user->id);

        // Create token to log the user in after verification
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'role' => $user->getRoleNames()->first() ?: 'customer',
                    'profile_completed' => $user->profile_status === 'completed',
                    'needs_profile_completion' => $user->needsProfileCompletion(),
                    'tenant' => $user->tenant ? [
                        'id' => $user->tenant->id,
                        'uuid' => $user->tenant->uuid,
                        'subdomain' => $user->tenant->subdomain,
                        'company_name' => $user->tenant->company_name,
                        'plan' => $user->tenant->plan?->name ?: 'Free',
                    ] : null,
                ],
                'token' => $token,
            ],
            'meta' => [
                'status' => 'success',
                'message' => 'Email verified successfully. You are now logged in.',
            ],
        ]);
    }

    /**
     * Resend verification email (PUBLIC - no auth required).
     */
    public function resendPublic(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'meta' => [
                    'status' => 'info',
                    'message' => 'Email already verified.',
                ],
            ]);
        }

        // Generate verification code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store the code in cache for 60 minutes
        cache()->put('email_verification_code_' . $user->id, $code, 3600);

        // Send verification email
        $user->sendEmailVerificationNotification();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Verification code sent to your email.',
                // Include code in development for testing
                'code' => app()->environment('local', 'development') ? $code : null,
            ],
        ]);
    }
}
