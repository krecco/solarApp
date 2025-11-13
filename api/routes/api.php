<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminDocumentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerDocumentController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\ExtrasController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\InvestmentController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OtpAuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\PreferenceController;
use App\Http\Controllers\Api\RepaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingsController;
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

    // Public Settings (accessible without authentication)
    Route::get('/settings/public', [SettingsController::class, 'publicSettings']);

    // Languages (public - accessible without authentication)
    Route::prefix('languages')->group(function () {
        Route::get('/', [LanguageController::class, 'index']); // Get all active languages
        Route::get('/default', [LanguageController::class, 'getDefault']); // Get default language
    });
});

// Protected routes (require authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // User Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserProfileController::class, 'show']); // Get full profile
        Route::put('/', [UserProfileController::class, 'update']); // Update profile
        Route::put('/password', [UserProfileController::class, 'updatePassword']); // Update password
    });

    // Customer Documents (Verification Documents)
    Route::prefix('documents')->group(function () {
        Route::get('/requirements', [CustomerDocumentController::class, 'requirements']); // Get document requirements with status
        Route::get('/summary', [CustomerDocumentController::class, 'summary']); // Get verification summary
        Route::get('/types', [CustomerDocumentController::class, 'documentTypes']); // Get available document types
        Route::get('/', [CustomerDocumentController::class, 'index']); // List user's uploaded documents
        Route::post('/upload', [CustomerDocumentController::class, 'upload']); // Upload document
        Route::get('/{file}', [CustomerDocumentController::class, 'show']); // Get document details
        Route::get('/{file}/download', [CustomerDocumentController::class, 'download']); // Download document
        Route::delete('/{file}', [CustomerDocumentController::class, 'destroy']); // Delete unverified document
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

    // User Language Preferences (authenticated)
    Route::prefix('languages')->group(function () {
        Route::get('/me', [LanguageController::class, 'show']); // Get user's language preferences
        Route::put('/me', [LanguageController::class, 'update']); // Update user's language preferences
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

    // Extras/Add-ons (All can view active, admin can manage)
    Route::prefix('extras')->group(function () {
        Route::get('/active', [ExtrasController::class, 'activeExtras']); // Public active extras
        Route::get('/', [ExtrasController::class, 'index']); // List all extras (with filters)
        Route::get('/{extra}', [ExtrasController::class, 'show']); // View extra details
        Route::get('/{extra}/usage', [ExtrasController::class, 'usage']); // View extra usage statistics

        // Admin only routes
        Route::middleware('role:admin')->group(function () {
            Route::post('/', [ExtrasController::class, 'store']); // Create extra
            Route::put('/{extra}', [ExtrasController::class, 'update']); // Update extra
            Route::delete('/{extra}', [ExtrasController::class, 'destroy']); // Delete extra
            Route::post('/{extra}/toggle-active', [ExtrasController::class, 'toggleActive']); // Toggle active status
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

    // PDF Document Generation
    Route::prefix('pdf')->group(function () {
        Route::get('/languages', [PdfController::class, 'availableLanguages']); // Get available languages
        Route::get('/investments/{investment}/contract', [PdfController::class, 'generateInvestmentContract']); // Generate investment contract
        Route::get('/investments/{investment}/repayment-schedule', [PdfController::class, 'generateRepaymentSchedule']); // Generate repayment schedule
    });

    // File Management
    Route::prefix('files')->group(function () {
        Route::get('/', [FileController::class, 'index']); // List files in container
        Route::post('/upload', [FileController::class, 'upload']); // Upload file
        Route::get('/{file}/download', [FileController::class, 'download']); // Download file
        Route::delete('/{file}', [FileController::class, 'destroy']); // Delete file

        // Admin and Manager only routes
        Route::middleware('role:admin|manager')->group(function () {
            Route::post('/{file}/verify', [FileController::class, 'verify']); // Verify file
        });
    });

    // Reports and Analytics
    Route::prefix('reports')->group(function () {
        Route::get('/dashboard', [ReportController::class, 'dashboard']); // Dashboard overview
        Route::get('/investments/analytics', [ReportController::class, 'investmentAnalytics']); // Investment analytics
        Route::get('/repayments/analytics', [ReportController::class, 'repaymentAnalytics']); // Repayment analytics
        Route::get('/plants/analytics', [ReportController::class, 'plantAnalytics']); // Solar plant analytics
        Route::get('/monthly/{year}/{month}', [ReportController::class, 'monthlyReport']); // Monthly report
        Route::get('/investments/{investment}/performance', [ReportController::class, 'investmentPerformance']); // Investment performance

        // Admin and Manager only routes
        Route::middleware('role:admin|manager')->group(function () {
            Route::post('/investments/export', [ReportController::class, 'exportInvestments'])->name('api.reports.export-investments'); // Export investments
            Route::get('/download/{filename}', [ReportController::class, 'downloadExport'])->name('api.reports.download'); // Download export
        });
    });

    // Activity Logs (Audit Trail)
    Route::prefix('activity-logs')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index']); // List all activity logs (with filters)
        Route::get('/statistics', [ActivityLogController::class, 'statistics']); // Activity statistics
        Route::get('/{activity}', [ActivityLogController::class, 'show']); // View single activity log
        Route::get('/model/{modelType}/{modelId}', [ActivityLogController::class, 'forModel']); // Activities for specific model
        Route::get('/user/{userId}', [ActivityLogController::class, 'byUser']); // Activities by specific user
    });

    // System Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index']); // Get all settings (or by group)
        Route::get('/{group}/{key}', [SettingsController::class, 'show']); // Get single setting

        // Admin only routes
        Route::middleware('role:admin')->group(function () {
            Route::post('/', [SettingsController::class, 'store']); // Create new setting
            Route::put('/{group}/{key}', [SettingsController::class, 'update']); // Update setting
            Route::delete('/{group}/{key}', [SettingsController::class, 'destroy']); // Delete setting
            Route::post('/bulk-update', [SettingsController::class, 'bulkUpdate']); // Bulk update settings
            Route::post('/reset', [SettingsController::class, 'reset']); // Reset to default
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

    // Admin Document Verification Routes (admin and manager)
    Route::prefix('admin/documents')->middleware('role:admin|manager')->group(function () {
        Route::get('/pending', [AdminDocumentController::class, 'pendingDocuments']); // List pending documents
        Route::get('/rejected', [AdminDocumentController::class, 'rejectedDocuments']); // List rejected documents
        Route::get('/statistics', [AdminDocumentController::class, 'statistics']); // Verification statistics
        Route::get('/users/{user}/status', [AdminDocumentController::class, 'userVerificationStatus']); // User verification status
        Route::post('/files/{file}/verify', [AdminDocumentController::class, 'verify']); // Verify document
        Route::post('/files/{file}/reject', [AdminDocumentController::class, 'reject']); // Reject document with reason
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
