# Multilingual PDF Generation - Template Directory Approach
## Simpler Implementation with Language-Specific Templates

**Created:** 2025-11-12
**Approach:** Separate Blade template directories per language (de, en, es, fr)

---

## Architecture Decision

Instead of using Laravel's translation system (`__()` helpers), we use **separate template directories** for each language. This is simpler, cleaner, and more maintainable for PDF generation.

### Benefits:
✅ **Simpler** - No translation files needed for PDFs
✅ **Flexible** - Can adjust layout/formatting per language
✅ **Easier to edit** - Non-developers can edit HTML directly
✅ **Clear separation** - Each language completely independent
✅ **No translation lookups** - Faster PDF generation
✅ **Language-specific formatting** - Different date formats, number formats, etc.

### Template Structure:

```
resources/views/pdfs/
├── layouts/
│   └── contract-base.blade.php          # Shared layout & styles
│
├── en/                                    # English templates
│   ├── investment-contract.blade.php
│   ├── repayment-schedule.blade.php
│   ├── offer-calculation.blade.php
│   └── plant-contract.blade.php
│
├── de/                                    # German templates
│   ├── investment-contract.blade.php
│   ├── repayment-schedule.blade.php
│   ├── offer-calculation.blade.php
│   └── plant-contract.blade.php
│
├── es/                                    # Spanish templates
│   ├── investment-contract.blade.php
│   ├── repayment-schedule.blade.php
│   ├── offer-calculation.blade.php
│   └── plant-contract.blade.php
│
├── fr/                                    # French templates
│   ├── investment-contract.blade.php
│   ├── repayment-schedule.blade.php
│   ├── offer-calculation.blade.php
│   └── plant-contract.blade.php
│
└── si/                                    # Slovenian templates
    ├── investment-contract.blade.php
    ├── repayment-schedule.blade.php
    ├── offer-calculation.blade.php
    └── plant-contract.blade.php
```

---

## Implementation

### Step 1: Base Layout Template (Shared Styles)

```blade
{{-- resources/views/pdfs/layouts/contract-base.blade.php --}}
<!DOCTYPE html>
<html lang="@yield('locale', 'de')">
<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
            padding: 20mm;
        }

        /* Typography */
        h1 {
            font-size: 20pt;
            margin-bottom: 10mm;
            color: #1a1a1a;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 5mm;
        }

        h2 {
            font-size: 14pt;
            margin-top: 8mm;
            margin-bottom: 4mm;
            color: #2563eb;
        }

        h3 {
            font-size: 12pt;
            margin-top: 5mm;
            margin-bottom: 3mm;
            color: #333;
        }

        p {
            margin-bottom: 3mm;
        }

        /* Layout */
        .header {
            margin-bottom: 10mm;
        }

        .section {
            margin-bottom: 8mm;
            page-break-inside: avoid;
        }

        .party {
            margin-bottom: 5mm;
            padding: 3mm;
            background-color: #f8f9fa;
            border-left: 3px solid #3b82f6;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5mm;
        }

        table th,
        table td {
            padding: 3mm;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        table th {
            background-color: #3b82f6;
            color: white;
            font-weight: 600;
        }

        table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .repayment-table {
            font-size: 9pt;
        }

        .repayment-table th {
            background-color: #2563eb;
        }

        .repayment-table td {
            text-align: right;
        }

        .repayment-table td:first-child {
            text-align: left;
        }

        /* Signatures */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 15mm;
            page-break-inside: avoid;
        }

        .signature-box {
            display: table-cell;
            width: 48%;
            padding: 5mm;
            vertical-align: top;
        }

        .signature-line {
            margin-top: 10mm;
            border-top: 1px solid #333;
            padding-top: 2mm;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 3mm;
        }

        /* Company Info */
        .company-info {
            font-size: 9pt;
            color: #6b7280;
            margin-bottom: 5mm;
        }

        /* Helpers */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: 600;
        }

        .text-primary {
            color: #3b82f6;
        }

        /* Page breaks */
        .page-break {
            page-break-after: always;
        }

        /* Language-specific adjustments */
        @yield('extra-styles')
    </style>
</head>
<body>
    @yield('content')

    <div class="footer">
        @yield('footer', 'Seite {PAGE_NUM} von {PAGE_COUNT}')
    </div>
</body>
</html>
```

---

### Step 2: German Template Example

```blade
{{-- resources/views/pdfs/de/investment-contract.blade.php --}}
@extends('pdfs.layouts.contract-base')

@section('locale', 'de')
@section('title', 'Investitionsvertrag')

@section('extra-styles')
/* German-specific styles if needed */
@endsection

@section('content')
<div class="company-info">
    {{ $companyInfo['name'] }}<br>
    {{ $companyInfo['address'] }}<br>
    Tel: {{ $companyInfo['phone'] }} | E-Mail: {{ $companyInfo['email'] }}
</div>

<div class="header">
    <h1>Investitionsvertrag</h1>
    <p><strong>Vertragsnummer:</strong> {{ $contractNumber }}</p>
    <p><strong>Vertragsdatum:</strong> {{ $contractDate->format('d.m.Y') }}</p>
</div>

<div class="section">
    <h2>Vertragsparteien</h2>

    <div class="party">
        <h3>Investor</h3>
        <p><strong>Name:</strong> {{ $investor->getFullNameWithTitlesAttribute() }}</p>
        @if($investor->addresses->isNotEmpty())
            @php $address = $investor->addresses->first(); @endphp
            <p><strong>Anschrift:</strong><br>
                {{ $address->street }} {{ $address->street_number }}<br>
                {{ $address->postal_code }} {{ $address->city }}<br>
                {{ $address->country }}
            </p>
        @endif
        <p><strong>E-Mail:</strong> {{ $investor->email }}</p>
        @if($investor->phone_nr)
            <p><strong>Telefon:</strong> {{ $investor->phone_nr }}</p>
        @endif
    </div>

    <div class="party">
        <h3>Anlagenbetreiber</h3>
        <p><strong>Name:</strong> {{ $plantOwner->getFullNameWithTitlesAttribute() }}</p>
        @if($plantOwner->addresses->isNotEmpty())
            @php $ownerAddress = $plantOwner->addresses->first(); @endphp
            <p><strong>Anschrift:</strong><br>
                {{ $ownerAddress->street }} {{ $ownerAddress->street_number }}<br>
                {{ $ownerAddress->postal_code }} {{ $ownerAddress->city }}<br>
                {{ $ownerAddress->country }}
            </p>
        @endif
    </div>
</div>

<div class="section">
    <h2>Investitionsgegenstand</h2>
    <p><strong>Photovoltaik-Anlage:</strong> {{ $solarPlant->title }}</p>
    <p><strong>Standort:</strong> {{ $solarPlant->location }}</p>
    <p><strong>Nennleistung:</strong> {{ number_format($solarPlant->nominal_power, 2, ',', '.') }} kWp</p>
    <p><strong>Geschätzte Jahresproduktion:</strong> {{ number_format($solarPlant->annual_production, 0, ',', '.') }} kWh</p>
</div>

<div class="section">
    <h2>Investitionsbedingungen</h2>
    <table>
        <tr>
            <td style="width: 60%;"><strong>Investitionssumme:</strong></td>
            <td class="text-right"><strong>{{ number_format($investment->amount, 2, ',', '.') }} €</strong></td>
        </tr>
        <tr>
            <td><strong>Laufzeit:</strong></td>
            <td class="text-right">{{ $investment->duration_months }} Monate</td>
        </tr>
        <tr>
            <td><strong>Zinssatz (p.a.):</strong></td>
            <td class="text-right">{{ number_format($investment->interest_rate, 2, ',', '.') }} %</td>
        </tr>
        <tr>
            <td><strong>Rückzahlungsintervall:</strong></td>
            <td class="text-right">
                @if($investment->repayment_interval === 'monthly')
                    Monatlich
                @elseif($investment->repayment_interval === 'quarterly')
                    Vierteljährlich
                @else
                    Jährlich
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Gesamtrückzahlung:</strong></td>
            <td class="text-right"><strong>{{ number_format($investment->total_repayment, 2, ',', '.') }} €</strong></td>
        </tr>
        <tr>
            <td><strong>Zinsen gesamt:</strong></td>
            <td class="text-right">{{ number_format($investment->total_interest, 2, ',', '.') }} €</td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>Rückzahlungsplan (Auszug - erste 10 Zahlungen)</h2>
    <table class="repayment-table">
        <thead>
            <tr>
                <th>Nr.</th>
                <th>Fälligkeitsdatum</th>
                <th>Hauptbetrag</th>
                <th>Zinsen</th>
                <th>Gesamt</th>
            </tr>
        </thead>
        <tbody>
            @foreach($repayments->take(10) as $repayment)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $repayment->due_date->format('d.m.Y') }}</td>
                <td>{{ number_format($repayment->principal, 2, ',', '.') }} €</td>
                <td>{{ number_format($repayment->interest, 2, ',', '.') }} €</td>
                <td><strong>{{ number_format($repayment->amount, 2, ',', '.') }} €</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($repayments->count() > 10)
    <p class="text-center" style="font-size: 9pt; color: #6b7280;">
        Der vollständige Rückzahlungsplan mit allen {{ $repayments->count() }} Zahlungen
        wird separat zur Verfügung gestellt.
    </p>
    @endif
</div>

<div class="section">
    <h2>Allgemeine Geschäftsbedingungen</h2>
    <div style="font-size: 9pt; line-height: 1.5;">
        {!! $termsAndConditions !!}
    </div>
</div>

<div class="page-break"></div>

<div class="signatures">
    <div class="signature-box">
        <p><strong>Investor</strong></p>
        <div class="signature-line">
            <p style="margin-top: 15mm;">_________________________________</p>
            <p>{{ $investor->name }}</p>
        </div>
        <p style="margin-top: 5mm;">Ort, Datum: _____________________</p>
    </div>

    <div class="signature-box">
        <p><strong>Anlagenbetreiber</strong></p>
        <div class="signature-line">
            <p style="margin-top: 15mm;">_________________________________</p>
            <p>{{ $companyInfo['name'] }}</p>
        </div>
        <p style="margin-top: 5mm;">Ort, Datum: _____________________</p>
    </div>
</div>
@endsection

@section('footer')
    {{ $companyInfo['name'] }} | Vertragsnummer: {{ $contractNumber }} | Seite {PAGE_NUM} von {PAGE_COUNT}
@endsection
```

---

### Step 3: English Template Example

```blade
{{-- resources/views/pdfs/en/investment-contract.blade.php --}}
@extends('pdfs.layouts.contract-base')

@section('locale', 'en')
@section('title', 'Investment Contract')

@section('content')
<div class="company-info">
    {{ $companyInfo['name'] }}<br>
    {{ $companyInfo['address'] }}<br>
    Phone: {{ $companyInfo['phone'] }} | Email: {{ $companyInfo['email'] }}
</div>

<div class="header">
    <h1>Investment Contract</h1>
    <p><strong>Contract Number:</strong> {{ $contractNumber }}</p>
    <p><strong>Contract Date:</strong> {{ $contractDate->format('Y-m-d') }}</p>
</div>

<div class="section">
    <h2>Parties</h2>

    <div class="party">
        <h3>Investor</h3>
        <p><strong>Name:</strong> {{ $investor->getFullNameWithTitlesAttribute() }}</p>
        @if($investor->addresses->isNotEmpty())
            @php $address = $investor->addresses->first(); @endphp
            <p><strong>Address:</strong><br>
                {{ $address->street }} {{ $address->street_number }}<br>
                {{ $address->postal_code }} {{ $address->city }}<br>
                {{ $address->country }}
            </p>
        @endif
        <p><strong>Email:</strong> {{ $investor->email }}</p>
        @if($investor->phone_nr)
            <p><strong>Phone:</strong> {{ $investor->phone_nr }}</p>
        @endif
    </div>

    <div class="party">
        <h3>Solar Plant Owner</h3>
        <p><strong>Name:</strong> {{ $plantOwner->getFullNameWithTitlesAttribute() }}</p>
        @if($plantOwner->addresses->isNotEmpty())
            @php $ownerAddress = $plantOwner->addresses->first(); @endphp
            <p><strong>Address:</strong><br>
                {{ $ownerAddress->street }} {{ $ownerAddress->street_number }}<br>
                {{ $ownerAddress->postal_code }} {{ $ownerAddress->city }}<br>
                {{ $ownerAddress->country }}
            </p>
        @endif
    </div>
</div>

<div class="section">
    <h2>Investment Object</h2>
    <p><strong>Solar Plant:</strong> {{ $solarPlant->title }}</p>
    <p><strong>Location:</strong> {{ $solarPlant->location }}</p>
    <p><strong>Nominal Power:</strong> {{ number_format($solarPlant->nominal_power, 2, '.', ',') }} kWp</p>
    <p><strong>Estimated Annual Production:</strong> {{ number_format($solarPlant->annual_production, 0, '.', ',') }} kWh</p>
</div>

<div class="section">
    <h2>Investment Terms</h2>
    <table>
        <tr>
            <td style="width: 60%;"><strong>Investment Amount:</strong></td>
            <td class="text-right"><strong>€{{ number_format($investment->amount, 2, '.', ',') }}</strong></td>
        </tr>
        <tr>
            <td><strong>Duration:</strong></td>
            <td class="text-right">{{ $investment->duration_months }} months</td>
        </tr>
        <tr>
            <td><strong>Interest Rate (p.a.):</strong></td>
            <td class="text-right">{{ number_format($investment->interest_rate, 2, '.', ',') }}%</td>
        </tr>
        <tr>
            <td><strong>Repayment Interval:</strong></td>
            <td class="text-right">
                @if($investment->repayment_interval === 'monthly')
                    Monthly
                @elseif($investment->repayment_interval === 'quarterly')
                    Quarterly
                @else
                    Annually
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Total Repayment:</strong></td>
            <td class="text-right"><strong>€{{ number_format($investment->total_repayment, 2, '.', ',') }}</strong></td>
        </tr>
        <tr>
            <td><strong>Total Interest:</strong></td>
            <td class="text-right">€{{ number_format($investment->total_interest, 2, '.', ',') }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>Repayment Schedule (First 10 Payments)</h2>
    <table class="repayment-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Due Date</th>
                <th>Principal</th>
                <th>Interest</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($repayments->take(10) as $repayment)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $repayment->due_date->format('Y-m-d') }}</td>
                <td>€{{ number_format($repayment->principal, 2, '.', ',') }}</td>
                <td>€{{ number_format($repayment->interest, 2, '.', ',') }}</td>
                <td><strong>€{{ number_format($repayment->amount, 2, '.', ',') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($repayments->count() > 10)
    <p class="text-center" style="font-size: 9pt; color: #6b7280;">
        The complete repayment schedule with all {{ $repayments->count() }} payments
        will be provided separately.
    </p>
    @endif
</div>

<div class="section">
    <h2>Terms and Conditions</h2>
    <div style="font-size: 9pt; line-height: 1.5;">
        {!! $termsAndConditions !!}
    </div>
</div>

<div class="page-break"></div>

<div class="signatures">
    <div class="signature-box">
        <p><strong>Investor</strong></p>
        <div class="signature-line">
            <p style="margin-top: 15mm;">_________________________________</p>
            <p>{{ $investor->name }}</p>
        </div>
        <p style="margin-top: 5mm;">Place, Date: _____________________</p>
    </div>

    <div class="signature-box">
        <p><strong>Solar Plant Owner</strong></p>
        <div class="signature-line">
            <p style="margin-top: 15mm;">_________________________________</p>
            <p>{{ $companyInfo['name'] }}</p>
        </div>
        <p style="margin-top: 5mm;">Place, Date: _____________________</p>
    </div>
</div>
@endsection

@section('footer')
    {{ $companyInfo['name'] }} | Contract No: {{ $contractNumber }} | Page {PAGE_NUM} of {PAGE_COUNT}
@endsection
```

---

### Step 4: Updated ContractGeneratorService

```php
<?php

namespace App\Services;

use App\Models\Investment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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

        // Prepare contract data
        $data = $this->prepareContractData($investment, $options, $locale);

        // Select template based on language
        $templatePath = "pdfs.{$locale}.investment-contract";

        // Generate PDF
        $pdf = Pdf::loadView($templatePath, $data);
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
    }

    /**
     * Generate repayment schedule PDF
     */
    public function generateRepaymentSchedule(
        Investment $investment,
        array $options = []
    ): string {
        $investment->load(['user', 'solarPlant', 'repayments']);
        $locale = $this->determineDocumentLanguage($investment, $options);
        $data = $this->prepareContractData($investment, $options, $locale);

        // Use language-specific template
        $templatePath = "pdfs.{$locale}.repayment-schedule";

        $pdf = Pdf::loadView($templatePath, $data);
        $pdf->setPaper('a4', 'portrait');

        $filename = "repayment-schedule_{$investment->id}_{$locale}_" . now()->format('Y-m-d') . ".pdf";
        $path = "repayments/{$investment->id}/{$filename}";

        Storage::disk('private')->put($path, $pdf->output());

        return $path;
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
        // 5. System default (English)

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

        return 'en'; // Default to English
    }

    /**
     * Validate language code against supported languages
     */
    protected function validateLanguage(string $code): string
    {
        $supported = ['en', 'de', 'es', 'fr', 'si'];
        return in_array($code, $supported) ? $code : 'en';
    }

    /**
     * Prepare contract data
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
        ];
    }

    /**
     * Generate contract number
     */
    protected function generateContractNumber(Investment $investment): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $id = substr($investment->id, 0, 8);

        return "INV-{$year}-{$month}-{$id}";
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
     * Get company info (could be from database/config per language)
     */
    protected function getCompanyInfo(string $locale): array
    {
        // You can store this in database or config files per language
        return [
            'name' => config('company.name'),
            'address' => config('company.address'),
            'phone' => config('company.phone'),
            'email' => config('company.email'),
            'website' => config('company.website'),
        ];
    }

    /**
     * Get terms and conditions in specified language
     */
    protected function getTermsAndConditions(string $locale): string
    {
        // Load from database or files
        // Could be stored in settings table or separate files
        $termsFile = resource_path("terms/{$locale}/investment-terms.html");

        if (file_exists($termsFile)) {
            return file_get_contents($termsFile);
        }

        return '';
    }
}
```

---

### Step 5: Usage Examples

```php
// In your controller or job
$contractService = app(ContractGeneratorService::class);

// Generate in user's preferred language
$path = $contractService->generateInvestmentContract($investment);

// Generate in specific language
$path = $contractService->generateInvestmentContract(
    $investment,
    ['language' => 'en']
);

// Generate in multiple languages
foreach (['de', 'en', 'es', 'fr'] as $lang) {
    $path = $contractService->generateInvestmentContract(
        $investment,
        ['language' => $lang]
    );
}
```

---

## Template Creation Checklist

For each new PDF document type, create 5 language versions:

- [ ] `resources/views/pdfs/en/{template-name}.blade.php`
- [ ] `resources/views/pdfs/de/{template-name}.blade.php`
- [ ] `resources/views/pdfs/es/{template-name}.blade.php`
- [ ] `resources/views/pdfs/fr/{template-name}.blade.php`
- [ ] `resources/views/pdfs/si/{template-name}.blade.php`

### Required Templates:

1. **Investment Contract** (`investment-contract.blade.php`)
   - Contract details
   - Parties information
   - Investment terms
   - Repayment schedule summary
   - Signatures

2. **Repayment Schedule** (`repayment-schedule.blade.php`)
   - Full repayment table
   - Payment instructions
   - Summary statistics

3. **Offer/Calculation** (`offer-calculation.blade.php`)
   - Solar plant details
   - Cost breakdown
   - ROI calculations
   - Offer validity

4. **Plant Contract** (`plant-contract.blade.php`)
   - For plant owners
   - Plant specifications
   - Usage rights
   - Maintenance terms

---

## Language-Specific Formatting

Different languages may require different formatting:

```blade
{{-- German (de) --}}
{{ number_format($amount, 2, ',', '.') }} €     // 1.234,56 €
{{ $date->format('d.m.Y') }}                     // 12.11.2025

{{-- English (en) --}}
€{{ number_format($amount, 2, '.', ',') }}       // €1,234.56
{{ $date->format('Y-m-d') }}                     // 2025-11-12

{{-- Spanish (es) --}}
{{ number_format($amount, 2, ',', '.') }} €      // 1.234,56 €
{{ $date->format('d/m/Y') }}                     // 12/11/2025

{{-- French (fr) --}}
{{ number_format($amount, 2, ',', ' ') }} €      // 1 234,56 €
{{ $date->format('d/m/Y') }}                     // 12/11/2025

{{-- Slovenian (si) --}}
{{ number_format($amount, 2, ',', '.') }} €      // 1.234,56 €
{{ $date->format('d. m. Y') }}                   // 12. 11. 2025
```

---

## Maintenance Tips

### When Adding New Content:
1. Update the English template first (primary/default language)
2. Copy to other language folders (de, es, fr, si)
3. Translate text content only (keep HTML structure identical)
4. Test PDF generation in all 5 languages

### When Changing Layout:
1. Update `contract-base.blade.php` for shared changes
2. Test all templates to ensure inheritance works

### Version Control:
- Keep templates synchronized across languages
- Use comments to mark language-specific sections
- Document any layout differences between languages

---

## Benefits Summary

✅ **No Translation Files Needed** - Text is directly in templates
✅ **Flexible Layouts** - Each language can have custom formatting
✅ **Easy to Edit** - Non-technical users can edit HTML
✅ **Fast Generation** - No translation lookups during PDF creation
✅ **Clear Separation** - No mixing of languages
✅ **Shared Styles** - Base layout ensures consistency
✅ **Version Control Friendly** - Easy to track changes per language

---

## Estimated Implementation Time

| Task | Time |
|------|------|
| Create base layout template | 2 hours |
| Create English templates (4 docs) | 4 hours |
| Create German templates (4 docs) | 3 hours |
| Create Spanish templates (4 docs) | 3 hours |
| Create French templates (4 docs) | 3 hours |
| Create Slovenian templates (4 docs) | 3 hours |
| Update ContractGeneratorService | 2 hours |
| Testing all languages | 4 hours |
| **Total** | **24 hours (3 days)** |

This approach is **simpler and faster** than the translation-based approach!
