<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\InvestmentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OtpAuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PreferenceController;
use App\Http\Controllers\Api\RepaymentController;
use App\Http\Controllers\Api\SolarPlantController;
use App\Http\Controllers\Api\UserProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - SIMPLIFIED ADMIN PANEL
|--------------------------------------------------------------------------
| Simple authentication + admin dashboard with user management
|--------------------------------------------------------------------------
*/

// Apply API middleware to all routes in this file
Route::middleware([
    \Illuminate\Http\Middleware\HandleCors::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
])->group(function () {

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // OTP Authentication
    Route::prefix('otp')->group(function () {
        Route::post('/send', [OtpAuthController::class, 'sendOtp']);
        Route::post('/verify', [OtpAuthController::class, 'verifyOtp']);
        Route::post('/resend', [OtpAuthController::class, 'resendOtp']);
    });

    // Password Reset
    Route::prefix('password')->group(function () {
        Route::post('/forgot', [PasswordResetController::class, 'sendResetLink']);
        Route::post('/reset', [PasswordResetController::class, 'resetPassword']);
        Route::post('/validate-token', [PasswordResetController::class, 'validateToken']);
    });

    // Email Verification (PUBLIC - users are not logged in when verifying)
    Route::post('/email/verify', [EmailVerificationController::class, 'verifyPublic']);
    Route::post('/email/resend', [EmailVerificationController::class, 'resendPublic']);
});

// Protected routes (require authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // User Profile
    Route::prefix('profile')->group(function () {
        Route::put('/', [UserProfileController::class, 'update']);
        Route::put('/password', [UserProfileController::class, 'updatePassword']);
    });

    // Email Verification (authenticated - for re-verification if needed)
    Route::post('/email/verify-authenticated', [EmailVerificationController::class, 'verify']);
    Route::post('/email/resend-authenticated', [EmailVerificationController::class, 'resend']);

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/clear-read', [NotificationController::class, 'clearRead']);
    });

    // User Preferences
    Route::prefix('preferences')->group(function () {
        Route::get('/', [PreferenceController::class, 'show']);
        Route::put('/', [PreferenceController::class, 'update']);
        Route::post('/reset', [PreferenceController::class, 'reset']);
    });

    // Solar Plants (All authenticated users can view, admin/manager can create/edit)
    Route::prefix('solar-plants')->group(function () {
        Route::get('/', [SolarPlantController::class, 'index']);
        Route::get('/statistics', [SolarPlantController::class, 'statistics']);
        Route::get('/{solarPlant}', [SolarPlantController::class, 'show']);

        // Admin and Manager only routes
        Route::middleware('role:admin|manager')->group(function () {
            Route::post('/', [SolarPlantController::class, 'store']);
            Route::put('/{solarPlant}', [SolarPlantController::class, 'update']);
            Route::delete('/{solarPlant}', [SolarPlantController::class, 'destroy']);
            Route::post('/{solarPlant}/status', [SolarPlantController::class, 'updateStatus']);
        });
    });

    // Investments (All authenticated users can view own, admin/manager can verify)
    Route::prefix('investments')->group(function () {
        Route::get('/', [InvestmentController::class, 'index']);
        Route::get('/statistics', [InvestmentController::class, 'statistics']);
        Route::post('/', [InvestmentController::class, 'store']);
        Route::get('/{investment}', [InvestmentController::class, 'show']);

        // Admin and Manager only routes
        Route::middleware('role:admin|manager')->group(function () {
            Route::put('/{investment}', [InvestmentController::class, 'update']);
            Route::delete('/{investment}', [InvestmentController::class, 'destroy']);
            Route::post('/{investment}/verify', [InvestmentController::class, 'verify']);
        });

        // Repayment routes
        Route::get('/{investment}/repayments', [RepaymentController::class, 'index']);
        Route::middleware('role:admin')->post('/{investment}/repayments/regenerate', [RepaymentController::class, 'regenerate']);
    });

    // Repayments (All authenticated users can view, admin/manager can manage)
    Route::prefix('repayments')->group(function () {
        Route::get('/statistics', [RepaymentController::class, 'statistics']);
        Route::get('/overdue', [RepaymentController::class, 'overdue']);
        Route::get('/upcoming', [RepaymentController::class, 'upcoming']);

        // Admin and Manager only routes
        Route::middleware('role:admin|manager')->group(function () {
            Route::post('/{repayment}/mark-paid', [RepaymentController::class, 'markAsPaid']);
        });
    });

    // System Admin Routes
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        // User Management
        Route::post('/users/search', [AdminController::class, 'users']);
        Route::get('/users/{user}', [AdminController::class, 'showUser']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
        Route::post('/users/{user}/send-welcome-email', [AdminController::class, 'sendWelcomeEmail']);
        Route::post('/users/{user}/avatar', [AdminController::class, 'updateAvatar']);
        Route::delete('/users/{user}/avatar', [AdminController::class, 'deleteAvatar']);
    });
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'version' => config('app.version', '1.0.0'),
    ]);
});

}); // End of API middleware group
