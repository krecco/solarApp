<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Repayment Schedule - Investment {{ substr($investment->id, 0, 8) }}</title>
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
            font-size: 22pt;
            margin: 0 0 10px 0;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }

        table th {
            background: #10b981;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 10pt;
        }

        table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        table tr:nth-child(even) {
            background: #f9fafb;
        }

        table tfoot {
            font-weight: bold;
            background: #f0fdf4;
        }

        table tfoot td {
            padding: 12px 8px;
            border-top: 2px solid #10b981;
        }

        .summary-box {
            background: #f0fdf4;
            border: 2px solid #10b981;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-row {
            display: table-row;
        }

        .summary-label {
            display: table-cell;
            padding: 8px;
            color: #666;
        }

        .summary-value {
            display: table-cell;
            padding: 8px;
            text-align: right;
            font-weight: bold;
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

        @page {
            margin: 20mm;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>REPAYMENT SCHEDULE</h1>
        <div style="font-size: 10pt; color: #999; margin-top: 10px;">
            Investment ID: {{ strtoupper(substr($investment->id, 0, 8)) }}<br>
            Generated: {{ $generatedAt->format('d.m.Y H:i') }}
        </div>
    </div>

    <!-- Investor Information -->
    <div class="section">
        <div class="section-title">Investor Information</div>

        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value"><strong>{{ $investor->name }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Customer No:</div>
                <div class="info-value">{{ $investor->customer_no }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $investor->email }}</div>
            </div>
        </div>
    </div>

    <!-- Investment Summary -->
    <div class="section">
        <div class="section-title">Investment Summary</div>

        <div class="summary-box">
            <div class="summary-grid">
                <div class="summary-row">
                    <div class="summary-label">Solar Plant:</div>
                    <div class="summary-value">{{ $solarPlant->title }}</div>
                </div>
                <div class="summary-row">
                    <div class="summary-label">Investment Amount:</div>
                    <div class="summary-value">€{{ number_format($investment->amount, 2, ',', '.') }}</div>
                </div>
                <div class="summary-row">
                    <div class="summary-label">Interest Rate:</div>
                    <div class="summary-value">{{ number_format($investment->interest_rate, 2) }}% p.a.</div>
                </div>
                <div class="summary-row">
                    <div class="summary-label">Duration:</div>
                    <div class="summary-value">{{ $investment->duration_months }} months</div>
                </div>
                <div class="summary-row">
                    <div class="summary-label">Repayment Interval:</div>
                    <div class="summary-value">{{ ucfirst($investment->repayment_interval) }}</div>
                </div>
                <div class="summary-row">
                    <div class="summary-label">Total Interest:</div>
                    <div class="summary-value" style="color: #059669;">€{{ number_format($investment->total_interest, 2, ',', '.') }}</div>
                </div>
                <div class="summary-row">
                    <div class="summary-label">Total Repayment:</div>
                    <div class="summary-value" style="color: #10b981; font-size: 14pt;">€{{ number_format($investment->total_repayment, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Repayment Schedule Table -->
    <div class="section">
        <div class="section-title">Detailed Repayment Schedule</div>

        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">No.</th>
                    <th style="width: 20%;">Due Date</th>
                    <th style="width: 20%;">Principal</th>
                    <th style="width: 20%;">Interest</th>
                    <th style="width: 20%;">Total</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPrincipal = 0;
                    $totalInterest = 0;
                    $totalAmount = 0;
                @endphp

                @foreach($repayments as $repayment)
                @php
                    $totalPrincipal += $repayment->principal_amount;
                    $totalInterest += $repayment->interest_amount;
                    $totalAmount += $repayment->amount;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $repayment->payment_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($repayment->due_date)->format('d.m.Y') }}</td>
                    <td style="text-align: right;">€{{ number_format($repayment->principal_amount, 2, ',', '.') }}</td>
                    <td style="text-align: right;">€{{ number_format($repayment->interest_amount, 2, ',', '.') }}</td>
                    <td style="text-align: right;"><strong>€{{ number_format($repayment->amount, 2, ',', '.') }}</strong></td>
                    <td style="text-align: center;">
                        @if($repayment->status === 'paid')
                            <span style="color: #10b981;">✓</span>
                        @elseif($repayment->status === 'overdue')
                            <span style="color: #ef4444;">⚠</span>
                        @else
                            <span style="color: #f59e0b;">○</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: right;"><strong>TOTAL:</strong></td>
                    <td style="text-align: right;">€{{ number_format($totalPrincipal, 2, ',', '.') }}</td>
                    <td style="text-align: right;">€{{ number_format($totalInterest, 2, ',', '.') }}</td>
                    <td style="text-align: right;"><strong>€{{ number_format($totalAmount, 2, ',', '.') }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top: 15px; font-size: 9pt; color: #666;">
            <strong>Status Legend:</strong><br>
            <span style="color: #10b981;">✓</span> = Paid |
            <span style="color: #f59e0b;">○</span> = Pending |
            <span style="color: #ef4444;">⚠</span> = Overdue
        </div>
    </div>

    <!-- Payment Instructions -->
    <div class="section">
        <div class="section-title">Payment Instructions</div>

        <p>Repayments will be processed automatically according to the SEPA mandate on file. Please ensure sufficient funds are available in your account before each due date.</p>

        <p style="margin-top: 15px;">If you have any questions regarding your repayment schedule, please contact our support team.</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        This is a computer-generated document and does not require a signature.<br>
        Generated on {{ $generatedAt->format('d.m.Y \a\t H:i') }}
    </div>
</body>
</html>
