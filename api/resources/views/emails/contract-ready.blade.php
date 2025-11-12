@extends('emails.layouts.base')

@section('title', 'Investment Contract Ready')

@section('header', 'Solar Planning - Contract Ready')

@section('content')
    <div class="greeting">
        Hello {{ $user->name }},
    </div>

    <div class="message">
        <p>Great news! Your investment contract has been generated and is now ready for download.</p>
    </div>

    <div class="code-box" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-color: #3b82f6;">
        <div class="code-label" style="color: #1e40af;">Investment Amount</div>
        <div class="code" style="color: #3b82f6; letter-spacing: normal; font-size: 28pt;">
            €{{ number_format($investment->amount, 2, ',', '.') }}
        </div>
    </div>

    <div class="info-section">
        <h3>Contract Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Solar Plant:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">{{ $solarPlant->title }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Investment Amount:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">€{{ number_format($investment->amount, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Interest Rate:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">{{ number_format($investment->interest_rate, 2) }}% p.a.</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Duration:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">{{ $investment->duration_months }} months</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Total Return:</td>
                <td style="padding: 8px 0; font-weight: 600; color: #10b981; text-align: right;">€{{ number_format($investment->total_repayment, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="alert-box info">
        <h4>Next Steps</h4>
        <ol style="margin: 8px 0 0 0; padding-left: 20px;">
            <li>Download your investment contract from your dashboard</li>
            <li>Review all terms and conditions carefully</li>
            <li>Sign the contract digitally or print and sign manually</li>
            <li>Upload the signed contract through your dashboard</li>
            <li>We'll verify your signature and activate your investment</li>
        </ol>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.frontend_url') }}/my/investments/{{ $investment->id }}" class="btn-primary">
            View & Download Contract
        </a>
    </div>

    <div class="info-section" style="background: #fef2f2; border: 1px solid #fecaca;">
        <h3 style="color: #991b1b;">Important Notice</h3>
        <p style="margin: 0; color: #7f1d1d;">
            Please review the contract carefully. By signing the contract, you agree to all terms and conditions outlined in the document.
            The repayment schedule will begin on the start date specified in the contract.
        </p>
    </div>

    <div class="message" style="margin-top: 30px;">
        <p>If you have any questions about the contract or need assistance, please don't hesitate to contact our support team.</p>
        <p style="font-size: 14px; color: #6b7280;">
            Email: {{ config('app.company_email', 'support@solarplanning.de') }}<br>
            Phone: {{ config('app.company_phone', '+49 30 12345678') }}
        </p>
    </div>

    <div class="message" style="margin-top: 20px;">
        <p><strong>Thank you for investing in sustainable energy!</strong></p>
    </div>
@endsection
