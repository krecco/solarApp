<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Facades\LogViewer;

Route::get('/', function () {
    return view('welcome');
});

// Named login route (fallback to prevent errors)
Route::get('/login', function () {
    return response()->json([
        'message' => 'Please authenticate using the API login endpoint: POST /api/v1/login',
        'error' => 'unauthenticated'
    ], 401);
})->name('login');

// Email Verification Routes (for API usage)
Route::prefix('email')->group(function () {
    // Verification notice (not really used in API context)
    Route::get('/verify', function () {
        return response()->json([
            'message' => 'Please verify your email through the link sent to your email address.'
        ]);
    })->middleware('auth:sanctum')->name('verification.notice');

    // Verify email with signed URL
    Route::get('/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        
        // Redirect to frontend success page or return JSON
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Email verified successfully!',
                'verified' => true
            ]);
        }
        
        // Redirect to frontend app
        return redirect(config('app.frontend_url', '/') . '/email-verified');
    })->middleware(['signed'])->name('verification.verify');

    // Resend verification email
    Route::post('/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.'
            ], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent!'
        ]);
    })->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');
});

// Log Viewer - Only for admin users
LogViewer::auth(function ($request) {
    // Check if user is authenticated and is admin
    return $request->user() && $request->user()->hasRole('admin');
});
