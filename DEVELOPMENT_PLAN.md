# Solar Planning App - Full Development Plan
## Laravel 12 API + Vue 3 Frontend Migration

**Created:** 2025-11-12
**Status:** Planning Phase
**Target Completion:** 16-20 weeks

---

## Executive Summary

This document outlines the complete development plan for migrating the Solar Planning application from Quarkus/Vue2 to Laravel 12/Vue3. The new architecture is already established with:

✅ **Completed Foundation:**
- Laravel 12 API with Sanctum authentication
- Vue 3 app with PrimeVue UI framework
- Role-based access control (admin, manager, user, customer)
- User management system
- Notification system
- Theme system with customization

🎯 **Migration Goals:**
- Migrate all solar planning features from old Quarkus backend
- Build unified Vue 3 frontend (merge admin + customer portals)
- Implement role-based access (admin vs customer views)
- Maintain data integrity during migration
- Improve UX with modern PrimeVue components

---

## Current State Analysis

### ✅ What We Have (New Stack)

**Laravel API (`/api`):**
- Laravel 12.x with PHP 8.2+
- PostgreSQL database setup
- Sanctum authentication (replaces Keycloak)
- User management with roles (admin, manager, user, customer)
- Spatie Laravel Permission for RBAC
- Email system with templates
- Notification system
- OTP authentication
- API versioning (/api/v1)
- Comprehensive testing setup

**Vue 3 App (`/app`):**
- Vue 3.5.20 with Vite 5.4
- PrimeVue 4.3.7 component library
- Pinia state management
- Vue Router 4 with role guards
- i18n support (4 languages)
- Theme system (8 color palettes)
- Admin dashboard template
- User CRUD operations
- Authentication flows complete
- TypeScript ready

### 🔄 What Needs Migration (Old Stack)

**From Quarkus Backend (`/Backend`):**
- 18 database entity models
- 18 REST resources (3,927 lines in SolarPlantResource alone)
- 16 business logic services
- 15 helper classes
- 50+ email templates
- PDF generation system
- Calendar/iCal integration
- Chart generation
- Scheduled tasks
- File upload/storage system

**Solar Planning Specific Features:**
- Solar plant management (CRUD + lifecycle)
- Investment management (tracking, repayments)
- Property owner relationships
- Repayment schedules and calculations
- Power consumption forecasts
- Contract generation (PDF)
- Document management (verification, contracts)
- SEPA payment integration
- Campaign management
- Settings & extras configuration
- Activity tracking
- Messaging system
- Dashboard analytics

---

## Role Architecture

### Role Mapping: Old → New

| Old System | New System | Access Level |
|------------|-----------|--------------|
| Backend Admin | `admin` | Full system access, user management, all CRUD |
| Backend Operator | `manager` | Plant/investment management, reports |
| Customer (Investor) | `customer` | View investments, profile, messaging |
| Customer (Plant Owner) | `customer` | View plants, documents, messaging |

### Role Configuration

Update `/api/config/roles.php`:

```php
return [
    'available' => [
        'admin',      // System administrators
        'manager',    // Solar plant managers/operators
        'customer',   // Investors and plant owners
    ],
    'default' => 'customer', // Default for registration
    'display_names' => [
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'customer' => 'Customer',
    ],
    'customer_types' => [
        'investor' => 'Investor',
        'plant_owner' => 'Plant Owner',
        'both' => 'Investor & Plant Owner',
    ],
];
```

### Access Matrix

| Feature | Admin | Manager | Customer |
|---------|-------|---------|----------|
| User Management | ✅ Full | ❌ None | ❌ None |
| Solar Plant Create/Edit | ✅ Full | ✅ Full | ❌ View Only |
| Investment Create/Edit | ✅ Full | ✅ Full | ❌ View Only |
| Document Verification | ✅ Full | ✅ Full | ❌ Upload Only |
| Repayment Management | ✅ Full | ✅ Full | ❌ View Only |
| Settings & Configuration | ✅ Full | ❌ None | ❌ None |
| Dashboard Analytics | ✅ All Data | ✅ Assigned | ✅ Own Data |
| Messaging | ✅ All | ✅ All | ✅ With Assigned Manager |

---

## Database Schema Migration

### Phase 1: Core Entities (Weeks 1-2)

**Priority 1: User Extensions**

```php
// Migration: add_solar_user_fields_to_users_table
Schema::table('users', function (Blueprint $table) {
    // Business info
    $table->string('title_prefix')->nullable();
    $table->string('title_suffix')->nullable();
    $table->string('phone_nr')->nullable();
    $table->tinyInteger('gender')->nullable();
    $table->boolean('is_business')->default(false);

    // Customer type
    $table->string('customer_type')->default('investor'); // investor, plant_owner, both

    // Verification
    $table->boolean('user_files_verified')->default(false);
    $table->timestamp('verified_at')->nullable();

    // Documents
    $table->text('document_extra_text_block_a')->nullable();
    $table->text('document_extra_text_block_b')->nullable();

    // Auto-generated customer number
    $table->integer('customer_no')->unique()->nullable();

    // Status (0 = active, 99 = deleted)
    $table->integer('status')->default(0);

    $table->timestamps();
    $table->softDeletes();
});

// Model: Update User model
class User extends Authenticatable {
    use HasApiTokens, HasRoles, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password',
        'title_prefix', 'title_suffix', 'phone_nr', 'gender',
        'is_business', 'customer_type',
        'user_files_verified', 'verified_at',
        'document_extra_text_block_a', 'document_extra_text_block_b',
        'customer_no', 'status',
    ];

    // Relationships
    public function solarPlants() { return $this->hasMany(SolarPlant::class); }
    public function investments() { return $this->hasMany(Investment::class); }
    public function addresses() { return $this->hasMany(UserAddress::class); }
    public function sepaPermissions() { return $this->hasMany(UserSepaPermission::class); }
}
```

**Priority 2: Solar Plant Core**

```php
// Model: SolarPlant
Schema::create('solar_plants', function (Blueprint $table) {
    $table->uuid('id')->primary();

    // Basic info
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('location')->nullable();
    $table->string('address')->nullable();

    // Technical specs
    $table->decimal('nominal_power', 10, 2); // kWp
    $table->decimal('annual_production', 12, 2)->nullable(); // kWh
    $table->decimal('consumption', 10, 2)->nullable(); // kWh

    // Financial
    $table->decimal('total_cost', 12, 2);
    $table->decimal('investment_needed', 12, 2)->nullable();
    $table->decimal('kwh_price', 8, 4)->nullable(); // Price per kWh
    $table->integer('contract_duration_years')->default(20);
    $table->decimal('interest_rate', 5, 2)->nullable();

    // Status & lifecycle
    $table->string('status')->default('draft'); // draft, active, funded, operational, completed
    $table->date('start_date')->nullable();
    $table->date('operational_date')->nullable();
    $table->date('end_date')->nullable();

    // Ownership
    $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Plant owner
    $table->foreignId('manager_id')->nullable()->constrained('users'); // Assigned manager

    // Documents & contracts
    $table->foreignUuid('file_container_id')->nullable();
    $table->boolean('contracts_signed')->default(false);
    $table->timestamp('contract_signed_at')->nullable();

    // Tracking
    $table->integer('rs')->default(0); // Record status (0=active, 99=deleted)

    $table->timestamps();
    $table->softDeletes();

    // Indexes
    $table->index(['user_id', 'status']);
    $table->index(['status', 'created_at']);
});

// Relationships
class SolarPlant extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'location', 'address',
        'nominal_power', 'annual_production', 'consumption',
        'total_cost', 'investment_needed', 'kwh_price',
        'contract_duration_years', 'interest_rate',
        'status', 'start_date', 'operational_date', 'end_date',
        'user_id', 'manager_id', 'file_container_id',
        'contracts_signed', 'contract_signed_at', 'rs',
    ];

    protected $casts = [
        'start_date' => 'date',
        'operational_date' => 'date',
        'end_date' => 'date',
        'contract_signed_at' => 'datetime',
        'contracts_signed' => 'boolean',
    ];

    // Relationships
    public function owner() { return $this->belongsTo(User::class, 'user_id'); }
    public function manager() { return $this->belongsTo(User::class, 'manager_id'); }
    public function propertyOwner() { return $this->hasOne(SolarPlantPropertyOwner::class); }
    public function extras() { return $this->hasMany(SolarPlantExtra::class); }
    public function investments() { return $this->hasMany(Investment::class); }
    public function repaymentData() { return $this->hasMany(SolarPlantRepaymentData::class); }
    public function fileContainer() { return $this->belongsTo(FileContainer::class); }
}
```

**Priority 3: Investment System**

```php
// Model: Investment
Schema::create('investments', function (Blueprint $table) {
    $table->uuid('id')->primary();

    // Investor
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    // Plant relationship
    $table->foreignUuid('solar_plant_id')->constrained()->cascadeOnDelete();

    // Investment details
    $table->decimal('amount', 12, 2);
    $table->integer('duration_months');
    $table->decimal('interest_rate', 5, 2);
    $table->string('repayment_interval')->default('monthly'); // monthly, quarterly, annually

    // Status
    $table->string('status')->default('pending'); // pending, verified, active, completed, cancelled
    $table->string('contract_status')->nullable();

    // Verification
    $table->boolean('verified')->default(false);
    $table->timestamp('verified_at')->nullable();
    $table->foreignId('verified_by')->nullable()->constrained('users');

    // Documents
    $table->foreignUuid('file_container_id')->nullable();

    // Dates
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();

    // Tracking
    $table->integer('rs')->default(0);

    $table->timestamps();
    $table->softDeletes();

    // Indexes
    $table->index(['user_id', 'status']);
    $table->index(['solar_plant_id', 'status']);
});

class Investment extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'solar_plant_id', 'amount', 'duration_months',
        'interest_rate', 'repayment_interval', 'status', 'contract_status',
        'verified', 'verified_at', 'verified_by',
        'file_container_id', 'start_date', 'end_date', 'rs',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationships
    public function user() { return $this->belongsTo(User::class); }
    public function solarPlant() { return $this->belongsTo(SolarPlant::class); }
    public function verifiedBy() { return $this->belongsTo(User::class, 'verified_by'); }
    public function repayments() { return $this->hasMany(InvestmentRepayment::class); }
    public function fileContainer() { return $this->belongsTo(FileContainer::class); }
}
```

### Phase 2: Supporting Entities (Weeks 3-4)

**File Management:**

```php
// Model: FileContainer
Schema::create('file_containers', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('name');
    $table->string('type'); // contracts, verification_docs, reports, etc.
    $table->text('description')->nullable();
    $table->timestamps();
});

// Model: File
Schema::create('files', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('file_container_id')->constrained()->cascadeOnDelete();

    $table->string('name');
    $table->string('original_name');
    $table->string('path');
    $table->string('mime_type');
    $table->bigInteger('size'); // bytes
    $table->string('extension');

    // Metadata
    $table->string('uploaded_by_type')->nullable(); // User, Admin, etc.
    $table->unsignedBigInteger('uploaded_by_id')->nullable();
    $table->boolean('is_verified')->default(false);
    $table->timestamp('verified_at')->nullable();

    $table->timestamps();

    $table->index(['file_container_id', 'created_at']);
});
```

**Repayment System:**

```php
// Model: SolarPlantRepaymentData
Schema::create('solar_plant_repayment_data', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('solar_plant_id')->constrained()->cascadeOnDelete();

    $table->decimal('amount', 12, 2);
    $table->date('due_date');
    $table->string('repayment_type'); // principal, interest, combined
    $table->string('status')->default('pending'); // pending, paid, overdue, cancelled

    $table->timestamps();
});

// Model: InvestmentRepayment
Schema::create('investment_repayments', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('investment_id')->constrained()->cascadeOnDelete();

    $table->decimal('amount', 12, 2);
    $table->decimal('principal', 12, 2);
    $table->decimal('interest', 12, 2);
    $table->date('due_date');
    $table->date('paid_date')->nullable();
    $table->string('status')->default('pending'); // pending, paid, overdue, cancelled

    $table->text('notes')->nullable();

    $table->timestamps();

    $table->index(['investment_id', 'due_date']);
    $table->index(['status', 'due_date']);
});

// Model: SolarPlantRepaymentLog
Schema::create('solar_plant_repayment_logs', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('solar_plant_id')->constrained()->cascadeOnDelete();
    $table->foreignUuid('solar_plant_repayment_data_id')->nullable();

    $table->decimal('amount', 12, 2);
    $table->date('payment_date');
    $table->string('payment_method')->nullable(); // bank_transfer, sepa, etc.
    $table->string('reference_number')->nullable();

    $table->timestamps();
});
```

**Additional Entities:**

```php
// UserAddress
Schema::create('user_addresses', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    $table->string('type')->default('billing'); // billing, shipping, property
    $table->string('street');
    $table->string('street_number')->nullable();
    $table->string('city');
    $table->string('postal_code');
    $table->string('country')->default('DE');

    $table->boolean('is_primary')->default(false);

    $table->timestamps();
});

// UserSepaPermission
Schema::create('user_sepa_permissions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    $table->string('iban');
    $table->string('bic')->nullable();
    $table->string('account_holder');
    $table->string('mandate_reference')->unique();
    $table->date('mandate_date');
    $table->boolean('is_active')->default(true);

    $table->timestamps();
});

// SolarPlantPropertyOwner
Schema::create('solar_plant_property_owners', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('solar_plant_id')->constrained()->cascadeOnDelete();

    $table->string('first_name');
    $table->string('last_name');
    $table->string('email')->nullable();
    $table->string('phone')->nullable();

    $table->text('notes')->nullable();

    $table->timestamps();
});

// SolarPlantExtra
Schema::create('solar_plant_extras', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('solar_plant_id')->constrained()->cascadeOnDelete();
    $table->foreignUuid('extra_id')->constrained('extras')->cascadeOnDelete();

    $table->decimal('price', 10, 2)->nullable();
    $table->integer('quantity')->default(1);

    $table->timestamps();
});

// Extra (service catalog)
Schema::create('extras', function (Blueprint $table) {
    $table->uuid('id')->primary();

    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('default_price', 10, 2);
    $table->string('unit')->default('piece'); // piece, hour, service
    $table->boolean('is_active')->default(true);

    $table->timestamps();
});

// Campaign
Schema::create('campaigns', function (Blueprint $table) {
    $table->uuid('id')->primary();

    $table->string('name');
    $table->text('description')->nullable();
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();
    $table->string('status')->default('draft'); // draft, active, completed

    $table->timestamps();
});

// Settings (system-wide key-value)
Schema::create('settings', function (Blueprint $table) {
    $table->uuid('id')->primary();

    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->string('type')->default('string'); // string, integer, boolean, json
    $table->text('description')->nullable();

    $table->timestamps();
});

// ActivityLog (audit trail)
Schema::create('activity_logs', function (Blueprint $table) {
    $table->uuid('id')->primary();

    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('action'); // created, updated, deleted, verified, etc.
    $table->string('subject_type'); // SolarPlant, Investment, User, etc.
    $table->string('subject_id')->nullable();

    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('ip_address')->nullable();
    $table->string('user_agent')->nullable();

    $table->timestamps();

    $table->index(['user_id', 'created_at']);
    $table->index(['subject_type', 'subject_id']);
});

// WebInfo (public pages)
Schema::create('web_info', function (Blueprint $table) {
    $table->uuid('id')->primary();

    $table->string('slug')->unique();
    $table->string('title');
    $table->longText('content');
    $table->boolean('is_published')->default(false);

    $table->timestamps();
});
```

### Phase 3: Migration Scripts (Week 5)

**Data Migration Command:**

```php
// app/Console/Commands/MigrateOldSolarData.php
php artisan migrate:solar-data

// Steps:
1. Export data from old PostgreSQL database
2. Map old UUIDs to new UUIDs (maintain relationships)
3. Migrate users (map Keycloak users to Laravel users)
4. Migrate solar plants
5. Migrate investments
6. Migrate file references
7. Migrate repayment history
8. Verify data integrity
```

---

## API Development Plan

### Phase 1: Core API Endpoints (Weeks 6-8)

**Solar Plant Management:**

```php
// routes/api.php - v1/solar-plants

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Solar Plants
    Route::prefix('solar-plants')->group(function () {
        Route::get('/', [SolarPlantController::class, 'index']);
        Route::post('/', [SolarPlantController::class, 'store'])->middleware('role:admin|manager');
        Route::get('/{solarPlant}', [SolarPlantController::class, 'show']);
        Route::put('/{solarPlant}', [SolarPlantController::class, 'update'])->middleware('role:admin|manager');
        Route::delete('/{solarPlant}', [SolarPlantController::class, 'destroy'])->middleware('role:admin|manager');

        // Status management
        Route::post('/{solarPlant}/status', [SolarPlantController::class, 'updateStatus'])->middleware('role:admin|manager');

        // Documents
        Route::get('/{solarPlant}/documents', [SolarPlantController::class, 'documents']);
        Route::post('/{solarPlant}/documents', [SolarPlantController::class, 'uploadDocument']);

        // Calculations
        Route::post('/{solarPlant}/calculate-forecast', [SolarPlantController::class, 'calculateForecast']);
        Route::get('/{solarPlant}/repayment-schedule', [SolarPlantController::class, 'repaymentSchedule']);

        // Contracts
        Route::post('/{solarPlant}/generate-contract', [SolarPlantController::class, 'generateContract'])->middleware('role:admin|manager');
    });

    // Investments
    Route::prefix('investments')->group(function () {
        Route::get('/', [InvestmentController::class, 'index']);
        Route::post('/', [InvestmentController::class, 'store']);
        Route::get('/{investment}', [InvestmentController::class, 'show']);
        Route::put('/{investment}', [InvestmentController::class, 'update'])->middleware('role:admin|manager');
        Route::delete('/{investment}', [InvestmentController::class, 'destroy'])->middleware('role:admin|manager');

        // Verification
        Route::post('/{investment}/verify', [InvestmentController::class, 'verify'])->middleware('role:admin|manager');

        // Documents
        Route::post('/{investment}/documents', [InvestmentController::class, 'uploadDocument']);

        // Repayments
        Route::get('/{investment}/repayments', [InvestmentController::class, 'repayments']);
    });

    // File Management
    Route::prefix('files')->group(function () {
        Route::post('/upload', [FileController::class, 'upload']);
        Route::get('/{file}', [FileController::class, 'show']);
        Route::get('/{file}/download', [FileController::class, 'download']);
        Route::delete('/{file}', [FileController::class, 'destroy'])->middleware('role:admin|manager');
    });

    // Settings (Admin only)
    Route::prefix('settings')->middleware('role:admin')->group(function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::put('/{key}', [SettingsController::class, 'update']);
    });

    // Extras (Service catalog)
    Route::prefix('extras')->group(function () {
        Route::get('/', [ExtrasController::class, 'index']);
        Route::post('/', [ExtrasController::class, 'store'])->middleware('role:admin');
        Route::put('/{extra}', [ExtrasController::class, 'update'])->middleware('role:admin');
        Route::delete('/{extra}', [ExtrasController::class, 'destroy'])->middleware('role:admin');
    });

    // Campaigns
    Route::prefix('campaigns')->middleware('role:admin|manager')->group(function () {
        Route::get('/', [CampaignController::class, 'index']);
        Route::post('/', [CampaignController::class, 'store']);
        Route::put('/{campaign}', [CampaignController::class, 'update']);
        Route::delete('/{campaign}', [CampaignController::class, 'destroy']);
    });

    // Activity Logs (Admin only)
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->middleware('role:admin');

    // Dashboard
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
});
```

**Controller Structure:**

```php
// app/Http/Controllers/Api/SolarPlantController.php
class SolarPlantController extends Controller {
    public function index(Request $request) {
        // For customers: only show their plants
        // For managers: show all plants
        $query = SolarPlant::with(['owner', 'manager']);

        if ($request->user()->hasRole('customer')) {
            $query->where('user_id', $request->user()->id);
        }

        return SolarPlantResource::collection(
            $query->paginate($request->get('per_page', 15))
        );
    }

    public function store(StoreSolarPlantRequest $request) {
        $solarPlant = SolarPlant::create($request->validated());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'subject_type' => SolarPlant::class,
            'subject_id' => $solarPlant->id,
            'new_values' => $solarPlant->toArray(),
        ]);

        return new SolarPlantResource($solarPlant);
    }

    public function calculateForecast(SolarPlant $solarPlant, Request $request) {
        // Business logic from old SolarPowerService
        $forecast = app(SolarForecastService::class)->calculate($solarPlant);
        return response()->json($forecast);
    }

    public function generateContract(SolarPlant $solarPlant) {
        // Generate PDF contract using laravel-dompdf
        $pdf = app(ContractGeneratorService::class)->generate($solarPlant);
        return $pdf->download("contract-{$solarPlant->id}.pdf");
    }
}
```

### Phase 2: Business Logic Services (Weeks 9-10)

**Service Layer:**

```php
// app/Services/SolarForecastService.php
class SolarForecastService {
    public function calculate(SolarPlant $plant): array {
        // Port logic from old SolarPowerService
        return [
            'annual_production' => $this->calculateAnnualProduction($plant),
            'monthly_breakdown' => $this->calculateMonthlyBreakdown($plant),
            'roi' => $this->calculateROI($plant),
        ];
    }
}

// app/Services/RepaymentCalculatorService.php
class RepaymentCalculatorService {
    public function generateSchedule(Investment $investment): Collection {
        // Port logic from old SolarPlantRepaymentService
        // Calculate monthly/quarterly/annual repayments
        // Include principal + interest breakdown
    }
}

// app/Services/ContractGeneratorService.php
class ContractGeneratorService {
    public function generate(SolarPlant $plant) {
        // Use Blade templates instead of Qute
        // Generate PDF using barryvdh/laravel-dompdf
        $pdf = PDF::loadView('contracts.solar-plant', compact('plant'));
        return $pdf;
    }
}

// app/Services/DocumentVerificationService.php
class DocumentVerificationService {
    public function verify(Investment $investment, User $verifiedBy): bool {
        $investment->update([
            'verified' => true,
            'verified_at' => now(),
            'verified_by' => $verifiedBy->id,
        ]);

        // Send notification to investor
        $investment->user->notify(new InvestmentVerifiedNotification($investment));

        return true;
    }
}
```

### Phase 3: Email & Notifications (Week 11)

**Email Templates (Blade):**

```php
// resources/views/emails/investment-verified.blade.php
// resources/views/emails/contract-ready.blade.php
// resources/views/emails/repayment-due.blade.php
// resources/views/emails/plant-operational.blade.php

// app/Mail/InvestmentVerified.php
class InvestmentVerified extends Mailable {
    use Queueable, SerializesModels;

    public function __construct(public Investment $investment) {}

    public function build() {
        return $this->subject('Investment Verified')
                    ->view('emails.investment-verified');
    }
}

// app/Notifications/InvestmentVerifiedNotification.php
class InvestmentVerifiedNotification extends Notification {
    public function via($notifiable) {
        return ['mail', 'database'];
    }

    public function toMail($notifiable) {
        return (new MailMessage)
            ->subject('Your investment has been verified')
            ->line('Your investment has been verified and is now active.')
            ->action('View Investment', url('/investments/'.$this->investment->id));
    }

    public function toArray($notifiable) {
        return [
            'investment_id' => $this->investment->id,
            'amount' => $this->investment->amount,
            'message' => 'Your investment has been verified',
        ];
    }
}
```

### Phase 4: Scheduled Tasks (Week 12)

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule) {
    // Check for due repayments
    $schedule->call(function () {
        app(RepaymentService::class)->processDueRepayments();
    })->daily();

    // Send repayment reminders
    $schedule->call(function () {
        app(RepaymentService::class)->sendReminders();
    })->dailyAt('09:00');

    // Update plant forecasts
    $schedule->call(function () {
        app(SolarForecastService::class)->updateAllForecasts();
    })->weekly();

    // Generate monthly reports
    $schedule->call(function () {
        app(ReportService::class)->generateMonthlyReports();
    })->monthlyOn(1, '00:00');
}
```

---

## Frontend Development Plan

### Phase 1: Core Pages & Routing (Weeks 13-14)

**Update Route Structure:**

```typescript
// src/router/admin.ts
export default [
  {
    path: '/admin/solar-plants',
    name: 'AdminSolarPlants',
    component: () => import('@/views/admin/solar-plants/SolarPlantList.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' },
  },
  {
    path: '/admin/solar-plants/new',
    name: 'AdminSolarPlantCreate',
    component: () => import('@/views/admin/solar-plants/SolarPlantCreate.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' },
  },
  {
    path: '/admin/solar-plants/:id',
    name: 'AdminSolarPlantDetail',
    component: () => import('@/views/admin/solar-plants/SolarPlantDetail.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' },
  },
  {
    path: '/admin/solar-plants/:id/edit',
    name: 'AdminSolarPlantEdit',
    component: () => import('@/views/admin/solar-plants/SolarPlantEdit.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' },
  },
  {
    path: '/admin/investments',
    name: 'AdminInvestments',
    component: () => import('@/views/admin/investments/InvestmentList.vue'),
    meta: { requiresAuth: true, requiresRole: 'admin' },
  },
  // ... more admin routes
]

// src/router/customer.ts (new file)
export default [
  {
    path: '/my-plants',
    name: 'MyPlants',
    component: () => import('@/views/customer/MyPlants.vue'),
    meta: { requiresAuth: true, requiresRole: 'customer' },
  },
  {
    path: '/my-investments',
    name: 'MyInvestments',
    component: () => import('@/views/customer/MyInvestments.vue'),
    meta: { requiresAuth: true, requiresRole: 'customer' },
  },
  {
    path: '/my-investments/:id',
    name: 'InvestmentDetail',
    component: () => import('@/views/customer/InvestmentDetail.vue'),
    meta: { requiresAuth: true, requiresRole: 'customer' },
  },
  // ... more customer routes
]
```

**Dashboard by Role:**

```typescript
// src/views/dashboard/MainDashboard.vue
<template>
  <AdminDashboard v-if="isAdmin" />
  <ManagerDashboard v-else-if="isManager" />
  <CustomerDashboard v-else />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRole } from '@/composables/useRole'

const { isAdmin, hasRole } = useRole()
const isManager = computed(() => hasRole('manager'))
</script>
```

### Phase 2: State Management (Weeks 15-16)

**Pinia Stores:**

```typescript
// src/stores/solarPlant.ts
export const useSolarPlantStore = defineStore('solarPlant', () => {
  const plants = ref<SolarPlant[]>([])
  const currentPlant = ref<SolarPlant | null>(null)
  const loading = ref(false)
  const pagination = ref({ page: 1, perPage: 15, total: 0 })

  async function fetchPlants(filters?: any) {
    loading.value = true
    try {
      const response = await solarPlantService.getPlants(filters, pagination.value)
      plants.value = response.data
      pagination.value = response.pagination
    } finally {
      loading.value = false
    }
  }

  async function createPlant(data: CreateSolarPlantDto) {
    const plant = await solarPlantService.create(data)
    plants.value.unshift(plant)
    return plant
  }

  async function updatePlant(id: string, data: UpdateSolarPlantDto) {
    const updated = await solarPlantService.update(id, data)
    const index = plants.value.findIndex(p => p.id === id)
    if (index !== -1) plants.value[index] = updated
    return updated
  }

  async function deletePlant(id: string) {
    await solarPlantService.delete(id)
    plants.value = plants.value.filter(p => p.id !== id)
  }

  return {
    plants,
    currentPlant,
    loading,
    pagination,
    fetchPlants,
    createPlant,
    updatePlant,
    deletePlant,
  }
})

// src/stores/investment.ts
export const useInvestmentStore = defineStore('investment', () => {
  // Similar structure to solarPlant store
  // ...
})
```

**API Services:**

```typescript
// src/api/solarPlant.service.ts
export const solarPlantService = {
  async getPlants(filters?: any, pagination?: any) {
    const params = { ...filters, ...pagination }
    const response = await api.get('/v1/solar-plants', { params })
    return response.data
  },

  async getPlant(id: string) {
    const response = await api.get(`/v1/solar-plants/${id}`)
    return response.data
  },

  async create(data: CreateSolarPlantDto) {
    const response = await api.post('/v1/solar-plants', data)
    return response.data
  },

  async update(id: string, data: UpdateSolarPlantDto) {
    const response = await api.put(`/v1/solar-plants/${id}`, data)
    return response.data
  },

  async delete(id: string) {
    await api.delete(`/v1/solar-plants/${id}`)
  },

  async updateStatus(id: string, status: string) {
    const response = await api.post(`/v1/solar-plants/${id}/status`, { status })
    return response.data
  },

  async calculateForecast(id: string) {
    const response = await api.post(`/v1/solar-plants/${id}/calculate-forecast`)
    return response.data
  },

  async generateContract(id: string) {
    const response = await api.post(`/v1/solar-plants/${id}/generate-contract`, {}, {
      responseType: 'blob'
    })
    return response.data
  },
}

// src/api/investment.service.ts
export const investmentService = {
  // Similar methods for investments
}
```

### Phase 3: Component Development (Weeks 17-18)

**Solar Plant Components:**

```vue
<!-- src/views/admin/solar-plants/SolarPlantList.vue -->
<template>
  <div class="solar-plant-list">
    <PageHeader title="Solar Plants">
      <Button
        label="New Plant"
        icon="pi pi-plus"
        @click="router.push('/admin/solar-plants/new')"
        v-if="isAdmin || isManager"
      />
    </PageHeader>

    <Card>
      <template #content>
        <!-- Filters -->
        <div class="filters">
          <InputText v-model="filters.search" placeholder="Search..." />
          <Dropdown v-model="filters.status" :options="statusOptions" placeholder="Status" />
        </div>

        <!-- DataTable -->
        <DataTable
          :value="plants"
          :loading="loading"
          :paginator="true"
          :rows="15"
          @page="onPage"
        >
          <Column field="title" header="Title" sortable />
          <Column field="nominal_power" header="Power (kWp)" sortable />
          <Column field="total_cost" header="Cost" sortable>
            <template #body="{ data }">
              {{ formatCurrency(data.total_cost) }}
            </template>
          </Column>
          <Column field="status" header="Status">
            <template #body="{ data }">
              <Tag :value="data.status" :severity="getStatusSeverity(data.status)" />
            </template>
          </Column>
          <Column header="Actions">
            <template #body="{ data }">
              <Button icon="pi pi-eye" @click="viewPlant(data.id)" text />
              <Button icon="pi pi-pencil" @click="editPlant(data.id)" text />
              <Button icon="pi pi-trash" @click="deletePlant(data.id)" text severity="danger" />
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useSolarPlantStore } from '@/stores/solarPlant'
import { useRole } from '@/composables/useRole'

const store = useSolarPlantStore()
const { isAdmin, isManager } = useRole()

const filters = ref({ search: '', status: '' })

onMounted(() => {
  store.fetchPlants()
})
</script>
```

```vue
<!-- src/views/admin/solar-plants/SolarPlantCreate.vue -->
<template>
  <div class="solar-plant-create">
    <PageHeader title="Create Solar Plant" />

    <Card>
      <template #content>
        <form @submit.prevent="handleSubmit">
          <!-- Basic Information -->
          <h3>Basic Information</h3>
          <div class="form-grid">
            <div class="field">
              <label for="title">Title *</label>
              <InputText id="title" v-model="form.title" required />
            </div>

            <div class="field">
              <label for="location">Location</label>
              <InputText id="location" v-model="form.location" />
            </div>

            <div class="field col-12">
              <label for="description">Description</label>
              <Textarea id="description" v-model="form.description" rows="3" />
            </div>
          </div>

          <!-- Technical Specifications -->
          <h3>Technical Specifications</h3>
          <div class="form-grid">
            <div class="field">
              <label for="nominal_power">Nominal Power (kWp) *</label>
              <InputNumber id="nominal_power" v-model="form.nominal_power" :min="0" :maxFractionDigits="2" />
            </div>

            <div class="field">
              <label for="annual_production">Annual Production (kWh)</label>
              <InputNumber id="annual_production" v-model="form.annual_production" :min="0" />
            </div>

            <div class="field">
              <label for="consumption">Consumption (kWh)</label>
              <InputNumber id="consumption" v-model="form.consumption" :min="0" />
            </div>
          </div>

          <!-- Financial Information -->
          <h3>Financial Information</h3>
          <div class="form-grid">
            <div class="field">
              <label for="total_cost">Total Cost (€) *</label>
              <InputNumber id="total_cost" v-model="form.total_cost" mode="currency" currency="EUR" />
            </div>

            <div class="field">
              <label for="kwh_price">kWh Price (€)</label>
              <InputNumber id="kwh_price" v-model="form.kwh_price" :minFractionDigits="4" />
            </div>

            <div class="field">
              <label for="contract_duration_years">Contract Duration (years)</label>
              <InputNumber id="contract_duration_years" v-model="form.contract_duration_years" :min="1" />
            </div>
          </div>

          <!-- Owner Selection -->
          <h3>Ownership</h3>
          <div class="form-grid">
            <div class="field">
              <label for="owner">Plant Owner *</label>
              <Dropdown
                id="owner"
                v-model="form.user_id"
                :options="customers"
                optionLabel="name"
                optionValue="id"
                filter
              />
            </div>

            <div class="field">
              <label for="manager">Assigned Manager</label>
              <Dropdown
                id="manager"
                v-model="form.manager_id"
                :options="managers"
                optionLabel="name"
                optionValue="id"
              />
            </div>
          </div>

          <!-- Actions -->
          <div class="form-actions">
            <Button label="Cancel" severity="secondary" @click="router.back()" />
            <Button label="Create" type="submit" :loading="loading" />
          </div>
        </form>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useSolarPlantStore } from '@/stores/solarPlant'
import { useToast } from 'primevue/usetoast'

const router = useRouter()
const store = useSolarPlantStore()
const toast = useToast()

const form = ref({
  title: '',
  description: '',
  location: '',
  nominal_power: 0,
  annual_production: 0,
  consumption: 0,
  total_cost: 0,
  kwh_price: 0,
  contract_duration_years: 20,
  user_id: null,
  manager_id: null,
})

const loading = ref(false)

async function handleSubmit() {
  loading.value = true
  try {
    await store.createPlant(form.value)
    toast.add({ severity: 'success', summary: 'Success', detail: 'Solar plant created', life: 3000 })
    router.push('/admin/solar-plants')
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to create plant', life: 3000 })
  } finally {
    loading.value = false
  }
}
</script>
```

**Customer View Components:**

```vue
<!-- src/views/customer/MyInvestments.vue -->
<template>
  <div class="my-investments">
    <PageHeader title="My Investments" />

    <!-- Stats Overview -->
    <div class="stats-grid">
      <StatCard
        title="Total Invested"
        :value="formatCurrency(totalInvested)"
        icon="pi pi-wallet"
      />
      <StatCard
        title="Active Investments"
        :value="activeInvestments"
        icon="pi pi-chart-line"
      />
      <StatCard
        title="Total Returns"
        :value="formatCurrency(totalReturns)"
        icon="pi pi-money-bill"
      />
      <StatCard
        title="Expected Yield"
        :value="`${expectedYield}%`"
        icon="pi pi-percentage"
      />
    </div>

    <!-- Investment List -->
    <Card>
      <template #content>
        <DataTable :value="investments" :loading="loading">
          <Column field="solar_plant.title" header="Solar Plant" />
          <Column field="amount" header="Investment">
            <template #body="{ data }">
              {{ formatCurrency(data.amount) }}
            </template>
          </Column>
          <Column field="interest_rate" header="Interest Rate">
            <template #body="{ data }">
              {{ data.interest_rate }}%
            </template>
          </Column>
          <Column field="status" header="Status">
            <template #body="{ data }">
              <Tag :value="data.status" :severity="getStatusSeverity(data.status)" />
            </template>
          </Column>
          <Column field="start_date" header="Start Date" />
          <Column header="Actions">
            <template #body="{ data }">
              <Button label="View Details" @click="viewInvestment(data.id)" text />
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>
  </div>
</template>
```

### Phase 4: Advanced Features (Weeks 19-20)

**Chart Integration:**

```vue
<!-- src/components/charts/PowerForecastChart.vue -->
<template>
  <Card>
    <template #header>
      <h3>Power Production Forecast</h3>
    </template>
    <template #content>
      <Chart type="line" :data="chartData" :options="chartOptions" />
    </template>
  </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Chart from 'primevue/chart'

const props = defineProps<{
  forecast: any
}>()

const chartData = computed(() => ({
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  datasets: [{
    label: 'Production (kWh)',
    data: props.forecast?.monthly_breakdown || [],
    borderColor: '#10b981',
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    tension: 0.4,
  }]
}))
</script>
```

**File Upload Components:**

```vue
<!-- src/components/files/FileUpload.vue -->
<template>
  <FileUpload
    name="file"
    :customUpload="true"
    @uploader="onUpload"
    :multiple="true"
    accept="application/pdf,image/*"
    :maxFileSize="10000000"
  >
    <template #empty>
      <p>Drag and drop files here to upload.</p>
    </template>
  </FileUpload>
</template>

<script setup lang="ts">
import { useToast } from 'primevue/usetoast'
import { fileService } from '@/api/file.service'

const toast = useToast()

async function onUpload(event: any) {
  const files = event.files
  const formData = new FormData()

  files.forEach((file: File) => {
    formData.append('files[]', file)
  })

  try {
    await fileService.upload(formData)
    toast.add({ severity: 'success', summary: 'Success', detail: 'Files uploaded', life: 3000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Upload failed', life: 3000 })
  }
}
</script>
```

---

## Testing Strategy

### Backend Testing (Weeks 21-22)

```php
// tests/Feature/SolarPlantTest.php
class SolarPlantTest extends TestCase {
    use RefreshDatabase;

    public function test_admin_can_create_solar_plant() {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/solar-plants', [
                'title' => 'Test Plant',
                'nominal_power' => 100,
                'total_cost' => 50000,
                // ... other fields
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('solar_plants', ['title' => 'Test Plant']);
    }

    public function test_customer_cannot_create_solar_plant() {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/solar-plants', [
                'title' => 'Test Plant',
            ]);

        $response->assertStatus(403);
    }

    public function test_customer_can_only_see_their_own_plants() {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $ownPlant = SolarPlant::factory()->create(['user_id' => $customer->id]);
        $otherPlant = SolarPlant::factory()->create();

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v1/solar-plants');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $ownPlant->id);
    }
}

// tests/Feature/InvestmentTest.php
// tests/Feature/RepaymentCalculatorTest.php
// tests/Unit/SolarForecastServiceTest.php
```

### Frontend Testing (Week 23)

```typescript
// src/components/__tests__/SolarPlantList.spec.ts
import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import SolarPlantList from '@/views/admin/solar-plants/SolarPlantList.vue'

describe('SolarPlantList', () => {
  it('renders plant list', async () => {
    const wrapper = mount(SolarPlantList, {
      global: {
        plugins: [createTestingPinia()],
      },
    })

    expect(wrapper.find('.solar-plant-list').exists()).toBe(true)
  })

  it('shows create button for admin', async () => {
    // Test implementation
  })
})
```

---

## Deployment Strategy

### Phase 1: Staging Deployment (Week 24)

**Docker Setup:**

```dockerfile
# docker-compose.yml
version: '3.8'

services:
  api:
    build: ./api
    ports:
      - "8000:8000"
    environment:
      - DB_CONNECTION=pgsql
      - DB_HOST=db
      - DB_DATABASE=solar_planning
    depends_on:
      - db
      - redis

  app:
    build: ./app
    ports:
      - "3000:3000"
    environment:
      - VITE_API_BASE_URL=http://api:8000

  db:
    image: postgres:15
    environment:
      - POSTGRES_DB=solar_planning
      - POSTGRES_PASSWORD=secret
    volumes:
      - db_data:/var/lib/postgresql/data

  redis:
    image: redis:7-alpine

volumes:
  db_data:
```

### Phase 2: Data Migration (Week 25)

1. Export old Keycloak users → Import to Laravel users
2. Migrate solar plants data
3. Migrate investments and repayments
4. Migrate files to Laravel storage
5. Verify data integrity
6. Run parallel systems for 1 week

### Phase 3: Production Deployment (Week 26)

1. DNS cutover
2. Monitor errors
3. User training
4. Documentation handoff

---

## Timeline Summary

| Phase | Duration | Key Deliverables |
|-------|----------|------------------|
| **Database Migration** | Weeks 1-5 | 18+ models, migrations, seeders |
| **API Development** | Weeks 6-12 | REST endpoints, services, jobs |
| **Frontend Core** | Weeks 13-16 | Pages, routing, state management |
| **Frontend Components** | Weeks 17-20 | CRUD forms, charts, file uploads |
| **Testing** | Weeks 21-23 | Backend + frontend tests |
| **Deployment** | Weeks 24-26 | Staging, data migration, production |

**Total: 26 weeks (6 months)**

---

## Risk Mitigation

### High Priority Risks

1. **Data Migration Complexity**
   - Mitigation: Run parallel systems, verify data checksums

2. **Calculation Logic Accuracy**
   - Mitigation: Port with extensive unit tests, compare outputs

3. **PDF Generation Differences**
   - Mitigation: Test all templates early, adjust styling

4. **User Training**
   - Mitigation: Create video tutorials, documentation, training sessions

---

## Success Criteria

✅ All features from old system migrated
✅ Zero data loss during migration
✅ 95%+ test coverage on critical paths
✅ <2s page load times
✅ Mobile responsive
✅ Role-based access working correctly
✅ PDF generation matches old contracts
✅ Email notifications working
✅ User acceptance sign-off

---

## Next Immediate Steps

1. ✅ Review and approve this plan
2. Create first database migrations (User extensions)
3. Create SolarPlant model + migration
4. Create Investment model + migration
5. Build first API endpoint (Solar Plants CRUD)
6. Build first Vue component (Solar Plant List)
7. Integrate and test end-to-end

---

**Document Version:** 1.0
**Last Updated:** 2025-11-12
**Status:** Ready for Development
