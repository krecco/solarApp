<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MailService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    protected MailService $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * Send password reset link to email.
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            // Don't reveal if email exists or not for security
            return response()->json([
                'meta' => [
                    'status' => 'success',
                    'message' => 'If the email exists in our system, a password reset link has been sent.',
                ],
            ]);
        }

        // Generate a password reset token
        $token = Str::random(64);
        
        // Store the token in cache for 1 hour
        cache()->put('password_reset_' . $user->email, [
            'token' => Hash::make($token),
            'created_at' => now(),
        ], 3600);

        // Generate the reset link with frontend URL
        $resetLink = rtrim(env('FRONTEND_WEB_URL', env('FRONTEND_URL', 'http://localhost:3000')), '/') 
                    . '/reset-password?token=' . $token 
                    . '&email=' . urlencode($user->email);

        // Send the email using MailService
        $success = $this->mailService->sendPasswordResetEmail(
            $user->email,
            $user->name,
            $resetLink
        );

        if (!$success) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Failed to send password reset email. Please try again.',
                ],
            ], 500);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Password reset link has been sent to your email.',
            ],
        ]);
    }

    /**
     * Reset password using token.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Get the stored reset data from cache
        $resetData = cache()->get('password_reset_' . $request->email);

        if (!$resetData) {
            throw ValidationException::withMessages([
                'token' => ['The password reset token is invalid or has expired.'],
            ]);
        }

        // Check if token is valid
        if (!Hash::check($request->token, $resetData['token'])) {
            throw ValidationException::withMessages([
                'token' => ['The password reset token is invalid.'],
            ]);
        }

        // Check if token is not expired (1 hour)
        if (now()->diffInMinutes($resetData['created_at']) > 60) {
            cache()->forget('password_reset_' . $request->email);
            throw ValidationException::withMessages([
                'token' => ['The password reset token has expired.'],
            ]);
        }

        // Update the user's password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Clear the reset token from cache
        cache()->forget('password_reset_' . $request->email);

        // Revoke all existing tokens
        $user->tokens()->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Password has been reset successfully. Please login with your new password.',
            ],
        ]);
    }

    /**
     * Validate password reset token.
     */
    public function validateToken(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $resetData = cache()->get('password_reset_' . $request->email);

        if (!$resetData) {
            return response()->json([
                'data' => [
                    'valid' => false,
                ],
                'meta' => [
                    'status' => 'error',
                    'message' => 'Invalid or expired token.',
                ],
            ], 400);
        }

        // Check if token is valid
        if (!Hash::check($request->token, $resetData['token'])) {
            return response()->json([
                'data' => [
                    'valid' => false,
                ],
                'meta' => [
                    'status' => 'error',
                    'message' => 'Invalid token.',
                ],
            ], 400);
        }

        // Check if token is not expired
        if (now()->diffInMinutes($resetData['created_at']) > 60) {
            cache()->forget('password_reset_' . $request->email);
            return response()->json([
                'data' => [
                    'valid' => false,
                ],
                'meta' => [
                    'status' => 'error',
                    'message' => 'Token has expired.',
                ],
            ], 400);
        }

        return response()->json([
            'data' => [
                'valid' => true,
                'expires_in' => 3600 - (now()->diffInSeconds($resetData['created_at'])),
            ],
            'meta' => [
                'status' => 'success',
                'message' => 'Token is valid.',
            ],
        ]);
    }
}
