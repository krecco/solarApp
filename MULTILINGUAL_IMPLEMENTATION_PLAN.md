# Solar App - Multilingual Implementation Plan
## Complete i18n Strategy for Backend, Frontend, and PDF Generation

**Created:** 2025-11-12
**Purpose:** Comprehensive multilingual support implementation plan

---

## Executive Summary

The Solar App requires full multilingual support across:
- **Backend API** - Laravel with multiple language support
- **Frontend App** - Vue 3 with i18n (already 4 languages implemented)
- **PDF Generation** - Multilingual contracts, offers, and documents
- **Email Notifications** - Multilingual email templates

**Supported Languages:**
1. 🇬🇧 **English (en)** - Default/fallback
2. 🇩🇪 **German (de)**
3. 🇪🇸 **Spanish (es)**
4. 🇫🇷 **French (fr)**
5. 🇸🇮 **Slovenian (si)**

---

## Current Implementation Status

### ✅ Frontend (Vue 3) - Fully Implemented
**Location:** `/app/src/locales/`

**Files:**
- `en.ts` - 1,727 lines (English)
- `de.ts` - 1,412 lines (German)
- `es.ts` - 1,374 lines (Spanish)
- `fr.ts` - 1,370 lines (French)
- `index.ts` - i18n configuration
- `types.ts` - TypeScript type definitions

**Features:**
- ✅ Vue i18n integration
- ✅ Typed translations (TypeScript)
- ✅ Runtime locale switching
- ✅ Comprehensive translation coverage
- ✅ Test suite for i18n

**Sample Structure:**
```typescript
const en: MessageSchema = {
  common: {
    appName: 'Admin Panel V2',
    welcome: 'Welcome',
    save: 'Save',
    cancel: 'Cancel',
    // ... 1700+ lines
  },
  auth: {
    login: 'Login',
    register: 'Register',
    // ...
  },
  // ... many more sections
}
```

### ⚠️ Backend (Laravel) - Partially Implemented

**Current State:**
- ✅ User preferences field exists (JSON)
- ✅ Laravel locale configuration (`config/app.php`)
- ❌ No language files (`lang/` directory missing)
- ❌ No translation helper usage
- ❌ No locale middleware
- ❌ No language table in database

**What Exists:**
```php
// User model has preferences field
'preferences' => 'array', // Can store: ['language' => 'de']

// Laravel config
'locale' => env('APP_LOCALE', 'en'),
'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
```

### ❌ PDF Generation - Not Multilingual

**Current Implementation:**
- ✅ Uses Laravel DomPDF (barryvdh/laravel-dompdf)
- ✅ Blade templates for PDFs
- ❌ No language parameter support
- ❌ Hardcoded German/English text
- ❌ No translation function usage

**Current Code:**
```php
// ContractGeneratorService.php
public function generateInvestmentContract(Investment $investment, array $options = []): string
{
    $data = $this->prepareContractData($investment, $options);

    // No locale consideration!
    $pdf = Pdf::loadView('pdfs.investment-contract', $data);

    return $path;
}
```

---

## Architecture Design

### 1. User Language Preference Storage

**Option A: JSON Preferences (Current - Recommended)**
```php
// User model - preferences column (already exists)
$user->preferences = [
    'language' => 'de',
    'timezone' => 'Europe/Berlin',
    'date_format' => 'DD.MM.YYYY',
    'currency' => 'EUR',
];
```

**Pros:**
- ✅ Already implemented
- ✅ Flexible for other preferences
- ✅ No new tables needed
- ✅ Easy to extend

**Cons:**
- ⚠️ No foreign key validation
- ⚠️ JSON querying less efficient

**Option B: Dedicated Language Column**
```php
// Add to users table migration
Schema::table('users', function (Blueprint $table) {
    $table->string('language', 2)->default('en');
    $table->index('language');
});
```

**Pros:**
- ✅ Indexed and queryable
- ✅ Database-level validation

**Cons:**
- ❌ Less flexible
- ❌ Requires migration

**✅ RECOMMENDATION: Keep using preferences JSON**
- Add validation rules for language codes
- Add helper methods to User model

### 2. Language Table (Optional - For Advanced Features)

```php
// Migration: create_languages_table.php
Schema::create('languages', function (Blueprint $table) {
    $table->id();
    $table->string('code', 2)->unique(); // 'en', 'de', 'es', 'fr'
    $table->string('name'); // 'English', 'Deutsch', etc.
    $table->string('native_name'); // 'English', 'Deutsch', 'Español', 'Français'
    $table->string('flag_emoji'); // '🇬🇧', '🇩🇪', '🇪🇸', '🇫🇷'
    $table->boolean('is_active')->default(true);
    $table->boolean('is_default')->default(false);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});

// Seed data
DB::table('languages')->insert([
    ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag_emoji' => '🇬🇧', 'is_active' => true, 'is_default' => true, 'sort_order' => 1],
    ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'flag_emoji' => '🇩🇪', 'is_active' => true, 'is_default' => false, 'sort_order' => 2],
    ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'flag_emoji' => '🇪🇸', 'is_active' => true, 'is_default' => false, 'sort_order' => 3],
    ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'flag_emoji' => '🇫🇷', 'is_active' => true, 'is_default' => false, 'sort_order' => 4],
    ['code' => 'si', 'name' => 'Slovenian', 'native_name' => 'Slovenščina', 'flag_emoji' => '🇸🇮', 'is_active' => true, 'is_default' => false, 'sort_order' => 5],
]);
```

**Use Cases for Language Table:**
- Admin UI to manage available languages
- Enable/disable languages without code changes
- Store language-specific settings (date formats, currency)
- Future expansion to more languages

**✅ RECOMMENDATION: Implement language table**
- Provides flexibility
- Better admin control
- Professional solution

### 3. Document Language Preference

**Challenge:** User may want documents in a different language than their UI language.

**Solution: Multiple Language Preferences**

```php
// User preferences structure
$user->preferences = [
    'ui_language' => 'en',          // Frontend language
    'document_language' => 'de',     // PDF/contract language
    'email_language' => 'de',        // Email notifications
    'timezone' => 'Europe/Berlin',
    // ... other preferences
];
```

**Alternative: Project-Level Setting**

```php
// Solar plant or investment can have its own language
Schema::table('solar_plants', function (Blueprint $table) {
    $table->string('document_language', 2)->nullable(); // Override user preference
});

Schema::table('investments', function (Blueprint $table) {
    $table->string('document_language', 2)->nullable();
});
```

**Use Case:**
- German user invests in Spanish solar plant
- Wants contract in Spanish
- But UI remains in German

**✅ RECOMMENDATION: Support both**
1. User preference as default
2. Per-document override option
3. Fallback chain: document → user → system default

---

## Implementation Plan

### Phase 1: Backend Language Infrastructure (2 days)

#### Step 1: Create Language Table & Model (0.5 day)

```php
// Migration: 2025_11_12_create_languages_table.php
public function up()
{
    Schema::create('languages', function (Blueprint $table) {
        $table->id();
        $table->string('code', 2)->unique();
        $table->string('name');
        $table->string('native_name');
        $table->string('flag_emoji');
        $table->boolean('is_active')->default(true);
        $table->boolean('is_default')->default(false);
        $table->integer('sort_order')->default(0);
        $table->timestamps();
    });
}

// Model: app/Models/Language.php
class Language extends Model
{
    protected $fillable = [
        'code', 'name', 'native_name', 'flag_emoji',
        'is_active', 'is_default', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getDefault(): ?self
    {
        return self::where('is_default', true)->first()
            ?? self::where('code', 'en')->first();
    }
}

// Seeder: database/seeders/LanguageSeeder.php
public function run()
{
    Language::insert([
        ['code' => 'en', 'name' => 'English', 'native_name' => 'English',
         'flag_emoji' => '🇬🇧', 'is_active' => true, 'is_default' => true, 'sort_order' => 1],
        ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch',
         'flag_emoji' => '🇩🇪', 'is_active' => true, 'is_default' => false, 'sort_order' => 2],
        ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español',
         'flag_emoji' => '🇪🇸', 'is_active' => true, 'is_default' => false, 'sort_order' => 3],
        ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français',
         'flag_emoji' => '🇫🇷', 'is_active' => true, 'is_default' => false, 'sort_order' => 4],
        ['code' => 'si', 'name' => 'Slovenian', 'native_name' => 'Slovenščina',
         'flag_emoji' => '🇸🇮', 'is_active' => true, 'is_default' => false, 'sort_order' => 5],
    ]);
}
```

#### Step 2: Update User Model with Language Helpers (0.5 day)

```php
// app/Models/User.php
class User extends Authenticatable
{
    // ... existing code ...

    /**
     * Get user's preferred UI language
     */
    public function getLanguage(): string
    {
        return $this->preferences['ui_language']
            ?? $this->preferences['language']
            ?? config('app.locale');
    }

    /**
     * Get user's preferred document language
     */
    public function getDocumentLanguage(): string
    {
        return $this->preferences['document_language']
            ?? $this->getLanguage();
    }

    /**
     * Get user's preferred email language
     */
    public function getEmailLanguage(): string
    {
        return $this->preferences['email_language']
            ?? $this->getLanguage();
    }

    /**
     * Set user language preference
     */
    public function setLanguage(string $code, string $type = 'ui'): void
    {
        $preferences = $this->preferences ?? [];
        $preferences["{$type}_language"] = $code;
        $this->preferences = $preferences;
        $this->save();
    }

    /**
     * Check if user has specific language preference
     */
    public function hasLanguagePreference(): bool
    {
        return isset($this->preferences['ui_language'])
            || isset($this->preferences['language']);
    }
}
```

#### Step 3: Create Locale Middleware (0.5 day)

```php
// app/Http/Middleware/SetLocale.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Priority order:
        // 1. Request header (Accept-Language)
        // 2. Query parameter (?lang=de)
        // 3. Authenticated user preference
        // 4. Default from config

        $locale = null;

        // Check query parameter
        if ($request->has('lang')) {
            $locale = $request->get('lang');
        }

        // Check authenticated user
        if (!$locale && $request->user()) {
            $locale = $request->user()->getLanguage();
        }

        // Check request header
        if (!$locale && $request->hasHeader('Accept-Language')) {
            $acceptLanguage = $request->header('Accept-Language');
            $locale = $this->parseAcceptLanguage($acceptLanguage);
        }

        // Fallback to config default
        if (!$locale) {
            $locale = config('app.locale');
        }

        // Validate against supported languages
        $supportedLocales = ['en', 'de', 'es', 'fr'];
        if (!in_array($locale, $supportedLocales)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }

    private function parseAcceptLanguage(string $header): ?string
    {
        // Parse "en-US,en;q=0.9,de;q=0.8" -> 'en'
        $languages = explode(',', $header);
        $parsed = [];

        foreach ($languages as $language) {
            $parts = explode(';', $language);
            $code = substr(trim($parts[0]), 0, 2); // Get first 2 chars (en-US -> en)
            $quality = 1.0;

            if (isset($parts[1])) {
                preg_match('/q=([0-9.]+)/', $parts[1], $matches);
                $quality = isset($matches[1]) ? (float) $matches[1] : 1.0;
            }

            $parsed[$code] = $quality;
        }

        arsort($parsed);
        return array_key_first($parsed);
    }
}

// Register in app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        // ... existing middleware
        \App\Http\Middleware\SetLocale::class,
    ],
];
```

#### Step 4: Create Laravel Language Files (0.5 day)

```bash
# Create language directories
mkdir -p /home/user/solarApp/api/lang/de
mkdir -p /home/user/solarApp/api/lang/en
mkdir -p /home/user/solarApp/api/lang/es
mkdir -p /home/user/solarApp/api/lang/fr
```

```php
// lang/en/contracts.php
return [
    'investment_contract' => 'Investment Contract',
    'contract_number' => 'Contract Number',
    'contract_date' => 'Contract Date',
    'parties' => 'Parties',
    'investor' => 'Investor',
    'plant_owner' => 'Solar Plant Owner',
    'investment_terms' => 'Investment Terms',
    'amount' => 'Investment Amount',
    'duration' => 'Duration',
    'interest_rate' => 'Interest Rate',
    'repayment_interval' => 'Repayment Interval',
    'repayment_schedule' => 'Repayment Schedule',
    'payment_number' => 'Payment #',
    'due_date' => 'Due Date',
    'principal' => 'Principal',
    'interest' => 'Interest',
    'total' => 'Total',
    'plant_details' => 'Solar Plant Details',
    'terms_and_conditions' => 'Terms and Conditions',
    'signatures' => 'Signatures',
    'investor_signature' => 'Investor Signature',
    'plant_owner_signature' => 'Plant Owner Signature',
    'date' => 'Date',
    'place' => 'Place',
];

// lang/de/contracts.php
return [
    'investment_contract' => 'Investitionsvertrag',
    'contract_number' => 'Vertragsnummer',
    'contract_date' => 'Vertragsdatum',
    'parties' => 'Vertragsparteien',
    'investor' => 'Investor',
    'plant_owner' => 'Anlagenbetreiber',
    'investment_terms' => 'Investitionsbedingungen',
    'amount' => 'Investitionssumme',
    'duration' => 'Laufzeit',
    'interest_rate' => 'Zinssatz',
    'repayment_interval' => 'Rückzahlungsintervall',
    'repayment_schedule' => 'Rückzahlungsplan',
    'payment_number' => 'Zahlung Nr.',
    'due_date' => 'Fälligkeitsdatum',
    'principal' => 'Hauptbetrag',
    'interest' => 'Zinsen',
    'total' => 'Gesamt',
    'plant_details' => 'Anlagendetails',
    'terms_and_conditions' => 'Allgemeine Geschäftsbedingungen',
    'signatures' => 'Unterschriften',
    'investor_signature' => 'Unterschrift Investor',
    'plant_owner_signature' => 'Unterschrift Anlagenbetreiber',
    'date' => 'Datum',
    'place' => 'Ort',
];

// lang/es/contracts.php - Spanish translations
// lang/fr/contracts.php - French translations
```

```php
// lang/en/emails.php
return [
    'greeting' => 'Hello',
    'regards' => 'Best regards',
    'investment_verified' => [
        'subject' => 'Your investment has been verified',
        'title' => 'Investment Verified',
        'message' => 'Your investment has been successfully verified.',
    ],
    'repayment_reminder' => [
        'subject' => 'Upcoming Repayment Due',
        'title' => 'Repayment Reminder',
        'message' => 'A repayment is due in :days days.',
    ],
];

// lang/de/emails.php
return [
    'greeting' => 'Hallo',
    'regards' => 'Mit freundlichen Grüßen',
    'investment_verified' => [
        'subject' => 'Ihre Investition wurde verifiziert',
        'title' => 'Investition verifiziert',
        'message' => 'Ihre Investition wurde erfolgreich verifiziert.',
    ],
    'repayment_reminder' => [
        'subject' => 'Anstehende Rückzahlung',
        'title' => 'Rückzahlungserinnerung',
        'message' => 'Eine Rückzahlung ist in :days Tagen fällig.',
    ],
];
```

---

### Phase 2: Multilingual PDF Generation (2 days)

#### Step 1: Update ContractGeneratorService (1 day)

```php
// app/Services/ContractGeneratorService.php
namespace App\Services;

use App\Models\Investment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class ContractGeneratorService
{
    /**
     * Generate investment contract PDF in specified language
     *
     * @param Investment $investment
     * @param array $options
     * @return string Path to generated PDF
     */
    public function generateInvestmentContract(
        Investment $investment,
        array $options = []
    ): string {
        // Load relationships
        $investment->load(['user', 'solarPlant', 'repayments']);

        // Determine language
        $locale = $this->determineDocumentLanguage($investment, $options);

        // Set locale for translations
        $originalLocale = App::getLocale();
        App::setLocale($locale);

        try {
            // Prepare contract data with translations
            $data = $this->prepareContractData($investment, $options, $locale);

            // Generate PDF with localized template
            $pdf = Pdf::loadView('pdfs.investment-contract', $data);
            $pdf->setPaper('a4', 'portrait');

            // Generate filename with language code
            $filename = $this->generateFilename($investment, $locale);

            // Save PDF to storage
            $path = "contracts/{$investment->id}/{$filename}";
            Storage::disk('private')->put($path, $pdf->output());

            // Update investment
            $investment->update([
                'contract_path' => $path,
                'contract_status' => 'generated',
                'contract_generated_at' => now(),
                'contract_language' => $locale,
            ]);

            // Log activity
            activity()
                ->performedOn($investment)
                ->withProperties([
                    'contract_path' => $path,
                    'language' => $locale,
                ])
                ->log('generated investment contract');

            return $path;

        } finally {
            // Restore original locale
            App::setLocale($originalLocale);
        }
    }

    /**
     * Determine which language to use for document
     */
    protected function determineDocumentLanguage(
        Investment $investment,
        array $options
    ): string {
        // Priority order:
        // 1. Explicit language parameter
        // 2. Investment document_language field
        // 3. User's document language preference
        // 4. User's general language preference
        // 5. System default

        if (isset($options['language'])) {
            return $this->validateLanguage($options['language']);
        }

        if (isset($investment->document_language)) {
            return $this->validateLanguage($investment->document_language);
        }

        if ($investment->user) {
            $userLang = $investment->user->getDocumentLanguage();
            return $this->validateLanguage($userLang);
        }

        return config('app.locale', 'en');
    }

    /**
     * Validate language code against supported languages
     */
    protected function validateLanguage(string $code): string
    {
        $supported = ['en', 'de', 'es', 'fr'];
        return in_array($code, $supported) ? $code : config('app.locale', 'en');
    }

    /**
     * Prepare contract data with translations
     */
    protected function prepareContractData(
        Investment $investment,
        array $options,
        string $locale
    ): array {
        return [
            'investment' => $investment,
            'investor' => $investment->user,
            'solarPlant' => $investment->solarPlant,
            'plantOwner' => $investment->solarPlant->owner,
            'repayments' => $investment->repayments,
            'contractDate' => $options['contract_date'] ?? now(),
            'contractNumber' => $this->generateContractNumber($investment),
            'companyInfo' => $this->getCompanyInfo($locale),
            'termsAndConditions' => $this->getTermsAndConditions($locale),
            'locale' => $locale,
            // Translations
            'trans' => [
                'contract_number' => __('contracts.contract_number'),
                'contract_date' => __('contracts.contract_date'),
                'parties' => __('contracts.parties'),
                'investor' => __('contracts.investor'),
                'plant_owner' => __('contracts.plant_owner'),
                'investment_terms' => __('contracts.investment_terms'),
                'amount' => __('contracts.amount'),
                'duration' => __('contracts.duration'),
                'interest_rate' => __('contracts.interest_rate'),
                'repayment_schedule' => __('contracts.repayment_schedule'),
                'payment_number' => __('contracts.payment_number'),
                'due_date' => __('contracts.due_date'),
                'principal' => __('contracts.principal'),
                'interest' => __('contracts.interest'),
                'total' => __('contracts.total'),
                // ... more translations
            ],
        ];
    }

    /**
     * Generate filename with language code
     */
    protected function generateFilename(Investment $investment, string $locale): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $contractNumber = substr($investment->id, 0, 8);

        return "contract_{$contractNumber}_{$locale}_{$timestamp}.pdf";
    }

    /**
     * Get company info in specified language
     */
    protected function getCompanyInfo(string $locale): array
    {
        // Could be stored in database or config per language
        return [
            'name' => __('company.name', [], $locale),
            'address' => __('company.address', [], $locale),
            'phone' => __('company.phone', [], $locale),
            'email' => __('company.email', [], $locale),
            'website' => __('company.website', [], $locale),
        ];
    }
}
```

#### Step 2: Update PDF Blade Template (0.5 day)

```blade
{{-- resources/views/pdfs/investment-contract.blade.php --}}
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <title>{{ $trans['investment_contract'] }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.6;
        }
        /* ... styles ... */
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $trans['investment_contract'] }}</h1>
        <p><strong>{{ $trans['contract_number'] }}:</strong> {{ $contractNumber }}</p>
        <p><strong>{{ $trans['contract_date'] }}:</strong> {{ $contractDate->format('d.m.Y') }}</p>
    </div>

    <div class="section">
        <h2>{{ $trans['parties'] }}</h2>

        <div class="party">
            <h3>{{ $trans['investor'] }}</h3>
            <p>{{ $investor->name }}</p>
            @if($investor->addresses->isNotEmpty())
                <p>{{ $investor->addresses->first()->full_address }}</p>
            @endif
        </div>

        <div class="party">
            <h3>{{ $trans['plant_owner'] }}</h3>
            <p>{{ $plantOwner->name }}</p>
        </div>
    </div>

    <div class="section">
        <h2>{{ $trans['investment_terms'] }}</h2>
        <table>
            <tr>
                <td>{{ $trans['amount'] }}:</td>
                <td>{{ number_format($investment->amount, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td>{{ $trans['duration'] }}:</td>
                <td>{{ $investment->duration_months }} {{ __('contracts.months') }}</td>
            </tr>
            <tr>
                <td>{{ $trans['interest_rate'] }}:</td>
                <td>{{ $investment->interest_rate }}%</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>{{ $trans['repayment_schedule'] }}</h2>
        <table class="repayment-table">
            <thead>
                <tr>
                    <th>{{ $trans['payment_number'] }}</th>
                    <th>{{ $trans['due_date'] }}</th>
                    <th>{{ $trans['principal'] }}</th>
                    <th>{{ $trans['interest'] }}</th>
                    <th>{{ $trans['total'] }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($repayments->take(10) as $repayment)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $repayment->due_date->format('d.m.Y') }}</td>
                    <td>{{ number_format($repayment->principal, 2, ',', '.') }} €</td>
                    <td>{{ number_format($repayment->interest, 2, ',', '.') }} €</td>
                    <td>{{ number_format($repayment->amount, 2, ',', '.') }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="signatures">
        <div class="signature-box">
            <p>{{ $trans['investor_signature'] }}</p>
            <div class="signature-line"></div>
            <p>{{ $trans['date'] }}: ______________</p>
            <p>{{ $trans['place'] }}: ______________</p>
        </div>

        <div class="signature-box">
            <p>{{ $trans['plant_owner_signature'] }}</p>
            <div class="signature-line"></div>
            <p>{{ $trans['date'] }}: ______________</p>
            <p>{{ $trans['place'] }}: ______________</p>
        </div>
    </div>
</body>
</html>
```

#### Step 3: Add Language Selection API (0.5 day)

```php
// app/Http/Controllers/Api/LanguageController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LanguageController extends Controller
{
    /**
     * Get all available languages
     */
    public function index(): JsonResponse
    {
        $languages = Language::active()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'languages' => $languages,
            'current' => app()->getLocale(),
            'default' => Language::getDefault()->code,
        ]);
    }

    /**
     * Update user's language preference
     */
    public function updateUserLanguage(Request $request): JsonResponse
    {
        $request->validate([
            'ui_language' => 'nullable|string|size:2',
            'document_language' => 'nullable|string|size:2',
            'email_language' => 'nullable|string|size:2',
        ]);

        $user = $request->user();
        $preferences = $user->preferences ?? [];

        if ($request->has('ui_language')) {
            $preferences['ui_language'] = $request->ui_language;
        }

        if ($request->has('document_language')) {
            $preferences['document_language'] = $request->document_language;
        }

        if ($request->has('email_language')) {
            $preferences['email_language'] = $request->email_language;
        }

        $user->preferences = $preferences;
        $user->save();

        return response()->json([
            'message' => 'Language preferences updated successfully',
            'preferences' => $preferences,
        ]);
    }

    /**
     * Generate document in specific language
     */
    public function generateDocument(Request $request): JsonResponse
    {
        $request->validate([
            'document_type' => 'required|in:contract,offer,report',
            'document_id' => 'required|uuid',
            'language' => 'required|string|size:2',
        ]);

        // Example for contract
        if ($request->document_type === 'contract') {
            $investment = Investment::findOrFail($request->document_id);

            $service = app(ContractGeneratorService::class);
            $path = $service->generateInvestmentContract(
                $investment,
                ['language' => $request->language]
            );

            return response()->json([
                'message' => 'Document generated successfully',
                'path' => $path,
                'language' => $request->language,
            ]);
        }

        return response()->json(['error' => 'Invalid document type'], 400);
    }
}

// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('languages', [LanguageController::class, 'index']);
    Route::put('user/language', [LanguageController::class, 'updateUserLanguage']);
    Route::post('documents/generate', [LanguageController::class, 'generateDocument']);
});
```

---

### Phase 3: Multilingual Email Notifications (1 day)

```php
// app/Mail/InvestmentVerifiedMail.php
namespace App\Mail;

use App\Models\Investment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class InvestmentVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Investment $investment;
    public string $locale;

    public function __construct(Investment $investment, ?string $locale = null)
    {
        $this->investment = $investment;
        $this->locale = $locale ?? $investment->user->getEmailLanguage();
    }

    public function build()
    {
        // Set locale for email
        App::setLocale($this->locale);

        return $this
            ->subject(__('emails.investment_verified.subject'))
            ->view('emails.investment-verified')
            ->with([
                'investment' => $this->investment,
                'locale' => $this->locale,
            ]);
    }
}

// resources/views/emails/investment-verified.blade.php
{{-- Email uses current locale set by mailable --}}
<h1>{{ __('emails.investment_verified.title') }}</h1>
<p>{{ __('emails.greeting') }}, {{ $investment->user->name }}</p>
<p>{{ __('emails.investment_verified.message') }}</p>
{{-- ... --}}
```

---

### Phase 4: Frontend Integration (1 day)

#### Language Selector Component

```vue
<!-- app/src/components/LanguageSelector.vue -->
<template>
  <Dropdown
    v-model="selectedLanguage"
    :options="languages"
    optionLabel="native_name"
    optionValue="code"
    placeholder="Select Language"
    @change="changeLanguage"
  >
    <template #value="{ value }">
      <div v-if="value" class="flex items-center gap-2">
        <span>{{ getLanguage(value)?.flag_emoji }}</span>
        <span>{{ getLanguage(value)?.native_name }}</span>
      </div>
    </template>
    <template #option="{ option }">
      <div class="flex items-center gap-2">
        <span>{{ option.flag_emoji }}</span>
        <span>{{ option.native_name }}</span>
      </div>
    </template>
  </Dropdown>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import Dropdown from 'primevue/dropdown'
import api from '@/api/client'

const { locale } = useI18n()
const languages = ref([])
const selectedLanguage = ref(locale.value)

onMounted(async () => {
  const response = await api.get('/api/languages')
  languages.value = response.data.languages
})

const changeLanguage = async () => {
  // Update frontend locale
  locale.value = selectedLanguage.value

  // Update user preference on backend
  await api.put('/api/user/language', {
    ui_language: selectedLanguage.value,
  })
}

const getLanguage = (code: string) => {
  return languages.value.find((l: any) => l.code === code)
}
</script>
```

#### Document Language Selector

```vue
<!-- In investment detail or contract generation form -->
<template>
  <div class="document-actions">
    <div class="field">
      <label>{{ $t('documents.select_language') }}</label>
      <Dropdown
        v-model="documentLanguage"
        :options="languages"
        optionLabel="native_name"
        optionValue="code"
        placeholder="Select Document Language"
      />
    </div>

    <Button
      label="Generate Contract"
      icon="pi pi-file-pdf"
      @click="generateContract"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import api from '@/api/client'

const documentLanguage = ref('de')

const generateContract = async () => {
  const response = await api.post('/api/documents/generate', {
    document_type: 'contract',
    document_id: props.investmentId,
    language: documentLanguage.value,
  })

  // Handle success
}
</script>
```

---

## Summary & Recommendations

### ✅ Recommended Implementation Order

1. **Week 1: Backend Infrastructure**
   - Create language table and seeder
   - Update User model with language helpers
   - Create SetLocale middleware
   - Create Laravel lang files (4 languages)

2. **Week 2: PDF Generation**
   - Update ContractGeneratorService with locale support
   - Create multilingual PDF templates
   - Add language parameter to all document generation

3. **Week 3: Email & Frontend**
   - Update all email mailables with locale support
   - Create language selector component
   - Add document language selection in frontend

### 📋 Configuration File

```php
// config/languages.php
return [
    'supported' => ['en', 'de', 'es', 'fr', 'si'],
    'default' => 'en',
    'fallback' => 'en',

    'names' => [
        'en' => 'English',
        'de' => 'Deutsch',
        'es' => 'Español',
        'fr' => 'Français',
        'si' => 'Slovenščina',
    ],

    'flags' => [
        'en' => '🇬🇧',
        'de' => '🇩🇪',
        'es' => '🇪🇸',
        'fr' => '🇫🇷',
        'si' => '🇸🇮',
    ],
];
```

### 🎯 Key Benefits

1. **User Preference**: Users can set different languages for UI, documents, and emails
2. **Document Override**: Generate same document in multiple languages
3. **Professional**: Proper localization with fallback chain
4. **Scalable**: Easy to add more languages
5. **Consistent**: Same language files used across emails, PDFs, and API responses

---

## Questions & Answers

### Q: Should we have a language table?
**A: YES** - Provides flexibility, admin control, and future-proofing

### Q: How to handle PDFs?
**A: Use Blade + DomPDF with Laravel's translation system** - Already using this approach, just need to add locale support

### Q: Separate language for documents?
**A: YES** - Store in user preferences: `ui_language`, `document_language`, `email_language`

### Q: Where to configure document language?
**A: Multiple places:**
1. User profile settings (default preference)
2. Per-document generation (override)
3. Investment/Solar Plant settings (optional)

---

**Total Implementation Time: 6-7 days**
- Backend infrastructure: 2 days
- PDF generation: 2 days
- Email notifications: 1 day
- Frontend integration: 1-2 days
