<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Investment Contract - {{ $contractNumber }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #10b981;
        }

        .header h1 {
            color: #10b981;
            font-size: 24pt;
            margin: 0 0 10px 0;
        }

        .header .contract-number {
            font-size: 14pt;
            color: #666;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #10b981;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            width: 40%;
            padding: 5px 10px 5px 0;
            font-weight: bold;
            color: #666;
        }

        .info-value {
            display: table-cell;
            padding: 5px 0;
        }

        .highlight-box {
            background: #f0fdf4;
            border: 2px solid #10b981;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }

        .highlight-box .amount {
            font-size: 20pt;
            font-weight: bold;
            color: #059669;
            text-align: center;
        }

        .highlight-box .label {
            text-align: center;
            color: #666;
            font-size: 10pt;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        table th {
            background: #10b981;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 10pt;
        }

        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10pt;
        }

        table tr:nth-child(even) {
            background: #f9fafb;
        }

        .terms {
            margin: 20px 0;
            padding: 15px;
            background: #f9fafb;
            border-left: 4px solid #10b981;
        }

        .terms ol {
            margin: 0;
            padding-left: 20px;
        }

        .terms li {
            margin-bottom: 8px;
        }

        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-box {
            display: inline-block;
            width: 45%;
            margin-top: 60px;
        }

        .signature-box.right {
            float: right;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 10pt;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #999;
            padding: 10px;
            border-top: 1px solid #e5e7eb;
        }

        .page-number:after {
            content: counter(page);
        }

        @page {
            margin: 20mm;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>INVESTMENT CONTRACT</h1>
        <div class="contract-number">Contract No: {{ $contractNumber }}</div>
        <div style="font-size: 10pt; color: #999; margin-top: 10px;">
            Date: {{ $contractDate->format('d.m.Y') }}
        </div>
    </div>

    <!-- Parties Section -->
    <div class="section">
        <div class="section-title">§1 Contracting Parties</div>

        <div style="margin-bottom: 20px;">
            <strong>Investor (Lender):</strong><br>
            @if($investor->is_business)
                <strong>{{ $investor->name }}</strong><br>
                @if($investor->company_registration)
                    Registration: {{ $investor->company_registration }}<br>
                @endif
            @else
                {{ $investor->title_prefix }} <strong>{{ $investor->name }}</strong> {{ $investor->title_suffix }}<br>
            @endif
            @if($investor->addresses->first())
                {{ $investor->addresses->first()->street }}<br>
                {{ $investor->addresses->first()->zip }} {{ $investor->addresses->first()->city }}<br>
            @endif
            Customer No: {{ $investor->customer_no }}<br>
            Email: {{ $investor->email }}
        </div>

        <div>
            <strong>Plant Owner (Borrower):</strong><br>
            @if($plantOwner->is_business)
                <strong>{{ $plantOwner->name }}</strong><br>
                @if($plantOwner->company_registration)
                    Registration: {{ $plantOwner->company_registration }}<br>
                @endif
            @else
                {{ $plantOwner->title_prefix }} <strong>{{ $plantOwner->name }}</strong> {{ $plantOwner->title_suffix }}<br>
            @endif
            @if($plantOwner->addresses->first())
                {{ $plantOwner->addresses->first()->street }}<br>
                {{ $plantOwner->addresses->first()->zip }} {{ $plantOwner->addresses->first()->city }}<br>
            @endif
            Email: {{ $plantOwner->email }}
        </div>
    </div>

    <!-- Solar Plant Information -->
    <div class="section">
        <div class="section-title">§2 Solar Plant</div>

        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Plant Name:</div>
                <div class="info-value">{{ $solarPlant->title }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Location:</div>
                <div class="info-value">{{ $solarPlant->location ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nominal Power:</div>
                <div class="info-value">{{ number_format($solarPlant->nominal_power, 2) }} kWp</div>
            </div>
            <div class="info-row">
                <div class="info-label">Expected Annual Production:</div>
                <div class="info-value">{{ number_format($solarPlant->expected_annual_production ?? 0) }} kWh</div>
            </div>
            <div class="info-row">
                <div class="info-label">Module Type:</div>
                <div class="info-value">{{ $solarPlant->module_type ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Number of Modules:</div>
                <div class="info-value">{{ $solarPlant->number_of_modules ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Investment Details -->
    <div class="section">
        <div class="section-title">§3 Investment Details</div>

        <div class="highlight-box">
            <div class="label">Investment Amount</div>
            <div class="amount">€{{ number_format($investment->amount, 2, ',', '.') }}</div>
        </div>

        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Interest Rate:</div>
                <div class="info-value">{{ number_format($investment->interest_rate, 2) }}% per annum</div>
            </div>
            <div class="info-row">
                <div class="info-label">Duration:</div>
                <div class="info-value">{{ $investment->duration_months }} months ({{ floor($investment->duration_months / 12) }} years)</div>
            </div>
            <div class="info-row">
                <div class="info-label">Repayment Interval:</div>
                <div class="info-value">{{ ucfirst($investment->repayment_interval) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Start Date:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($investment->start_date)->format('d.m.Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">End Date:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($investment->end_date)->format('d.m.Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Interest:</div>
                <div class="info-value"><strong>€{{ number_format($investment->total_interest, 2, ',', '.') }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Repayment:</div>
                <div class="info-value"><strong style="color: #10b981;">€{{ number_format($investment->total_repayment, 2, ',', '.') }}</strong></div>
            </div>
        </div>
    </div>

    <!-- Repayment Schedule Summary -->
    <div class="section">
        <div class="section-title">§4 Repayment Schedule</div>

        <p>The borrower commits to repaying the investment amount plus interest according to the following schedule:</p>

        <table>
            <thead>
                <tr>
                    <th>Payment No.</th>
                    <th>Due Date</th>
                    <th>Principal</th>
                    <th>Interest</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($repayments->take(10) as $repayment)
                <tr>
                    <td>{{ $repayment->payment_number }}/{{ $totalRepayments }}</td>
                    <td>{{ \Carbon\Carbon::parse($repayment->due_date)->format('d.m.Y') }}</td>
                    <td>€{{ number_format($repayment->principal_amount, 2, ',', '.') }}</td>
                    <td>€{{ number_format($repayment->interest_amount, 2, ',', '.') }}</td>
                    <td><strong>€{{ number_format($repayment->amount, 2, ',', '.') }}</strong></td>
                </tr>
                @endforeach
                @if($repayments->count() > 10)
                <tr>
                    <td colspan="5" style="text-align: center; font-style: italic; color: #666;">
                        ... and {{ $repayments->count() - 10 }} more payments (see full schedule attached)
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Terms and Conditions -->
    <div class="section">
        <div class="section-title">§5 Terms and Conditions</div>

        <div class="terms">
            <ol>
                @foreach($termsAndConditions as $term)
                <li>{{ $term }}</li>
                @endforeach
            </ol>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="section-title">§6 Signatures</div>

        <p>Both parties confirm that they have read and understood this contract and agree to all terms and conditions.</p>

        <div class="signature-box">
            <div class="signature-line">
                <div>Place, Date</div>
                <div style="margin-top: 10px;"><strong>Investor</strong></div>
                <div>{{ $investor->name }}</div>
            </div>
        </div>

        <div class="signature-box right">
            <div class="signature-line">
                <div>Place, Date</div>
                <div style="margin-top: 10px;"><strong>Plant Owner</strong></div>
                <div>{{ $plantOwner->name }}</div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        {{ $companyInfo['name'] }} | {{ $companyInfo['address'] }} |
        {{ $companyInfo['email'] }} | {{ $companyInfo['phone'] }} |
        Registration: {{ $companyInfo['registration'] }} | Tax ID: {{ $companyInfo['tax_id'] }}
    </div>
</body>
</html>
