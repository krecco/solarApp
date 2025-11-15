<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    protected LoggingService $loggingService;

    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    /**
     * Rate limiting key prefix.
     */
    const RATE_LIMIT_PREFIX = 'otp-request:';

    /**
     * Maximum OTP requests per hour.
     */
    const MAX_REQUESTS_PER_HOUR = 5;

    /**
     * Send OTP code to the given email.
     *
     * @throws \Exception
     */
    public function sendOtp(string $email): array
    {
        // Check if user exists
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            // Return success even if user doesn't exist (security best practice)
            return [
                'success' => true,
                'message' => 'If the email exists in our system, an OTP code has been sent.',
            ];
        }

        // Check rate limiting
        $rateLimitKey = self::RATE_LIMIT_PREFIX . $email;
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, self::MAX_REQUESTS_PER_HOUR)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            
            return [
                'success' => false,
                'message' => "Too many OTP requests. Please try again in {$seconds} seconds.",
                'retry_after' => $seconds,
            ];
        }

        // Create new OTP code
        $otpCode = OtpCode::createForEmail($email);

        // Send OTP email using MailService (same style as password reset)
        try {
            $mailService = app(MailService::class);
            $success = $mailService->sendOtpEmail($email, $user->name, $otpCode->code);

            if (!$success) {
                throw new \Exception('Failed to send OTP email');
            }

            // Increment rate limiter
            RateLimiter::hit($rateLimitKey, 3600); // 1 hour

            return [
                'success' => true,
                'message' => 'OTP code has been sent to your email.',
                'expires_in' => OtpCode::VALIDITY_MINUTES * 60, // in seconds
            ];
        } catch (\Exception $e) {
            $this->loggingService->emailError($email, 'OTP Code', $e);

            throw new \Exception('Failed to send OTP code. Please try again later.');
        }
    }

    /**
     * Verify OTP code and authenticate user.
     */
    public function verifyOtp(string $email, string $code): array
    {
        // Find valid OTP code
        $otpCode = OtpCode::where('email', $email)
                         ->where('code', $code)
                         ->valid()
                         ->first();

        if (!$otpCode) {
            // Check if there's any OTP for this email (for better error messages)
            $anyOtp = OtpCode::where('email', $email)->first();
            
            if (!$anyOtp) {
                return [
                    'success' => false,
                    'message' => 'Invalid OTP code.',
                ];
            }
            
            if ($anyOtp->isExpired()) {
                return [
                    'success' => false,
                    'message' => 'OTP code has expired. Please request a new one.',
                ];
            }
            
            if ($anyOtp->hasReachedMaxAttempts()) {
                return [
                    'success' => false,
                    'message' => 'Maximum verification attempts reached. Please request a new OTP.',
                ];
            }
            
            // Invalid code - increment attempts
            $anyOtp->incrementAttempts();
            
            return [
                'success' => false,
                'message' => 'Invalid OTP code.',
                'attempts_left' => OtpCode::MAX_ATTEMPTS - $anyOtp->attempts,
            ];
        }

        // OTP is valid - find and authenticate user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
            ];
        }

        // Delete all OTP codes for this email
        OtpCode::where('email', $email)->delete();

        // Create authentication token
        $token = $user->createToken('otp-auth-token')->plainTextToken;

        // Mark email as verified if not already
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return [
            'success' => true,
            'message' => 'Authentication successful.',
            'user' => $user->only(['id', 'name', 'email', 'email_verified_at', 'created_at']),
            'token' => $token,
        ];
    }

    /**
     * Clean up expired OTP codes.
     */
    public function cleanupExpiredCodes(): int
    {
        return OtpCode::where('expires_at', '<', now())
                     ->orWhere('attempts', '>=', OtpCode::MAX_ATTEMPTS)
                     ->delete();
    }
}
