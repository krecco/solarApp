<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OtpAuthController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Send OTP code to email (Step 1).
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        try {
            $result = $this->otpService->sendOtp($request->email);

            if (!$result['success']) {
                return response()->json([
                    'meta' => [
                        'status' => 'error',
                        'message' => $result['message'],
                    ],
                    'errors' => [
                        'email' => [$result['message']],
                    ],
                ], 429); // Too Many Requests
            }

            return response()->json([
                'data' => [
                    'expires_in' => $result['expires_in'] ?? 600, // 10 minutes in seconds
                ],
                'meta' => [
                    'status' => 'success',
                    'message' => $result['message'],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Verify OTP code and login (Step 2).
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'code' => 'required|string|size:6',
        ]);

        $result = $this->otpService->verifyOtp($request->email, $request->code);

        if (!$result['success']) {
            // Include attempts_left in the error message if available
            $errorMessage = $result['message'];
            if (isset($result['attempts_left'])) {
                $errorMessage .= ' Attempts left: ' . $result['attempts_left'];
            }

            throw ValidationException::withMessages([
                'code' => [$errorMessage],
            ]);
        }

        return response()->json([
            'data' => [
                'user' => $result['user'],
                'token' => $result['token'],
            ],
            'meta' => [
                'status' => 'success',
                'message' => $result['message'],
            ],
        ]);
    }

    /**
     * Resend OTP code.
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        try {
            $result = $this->otpService->sendOtp($request->email);

            if (!$result['success']) {
                return response()->json([
                    'meta' => [
                        'status' => 'error',
                        'message' => $result['message'],
                        'retry_after' => $result['retry_after'] ?? null,
                    ],
                ], 429);
            }

            return response()->json([
                'data' => [
                    'expires_in' => $result['expires_in'] ?? 600,
                ],
                'meta' => [
                    'status' => 'success',
                    'message' => 'A new OTP code has been sent.',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }
}
