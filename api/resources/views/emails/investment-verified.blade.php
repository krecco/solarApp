@extends('emails.layouts.base')

@section('title', 'Investment Verified')

@section('header', 'Solar Planning - Investment Verified')

@section('content')
    <div class="greeting">
        Hello {{ $user->name }},
    </div>

    <div class="message">
        <p>Great news! Your investment has been verified and is now active.</p>
    </div>

    <div class="info-section">
        <h3>Investment Details</h3>
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
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">{{ number_format($investment->interest_rate, 2) }}% per year</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Duration:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">{{ $investment->duration_months }} months</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Expected Return:</td>
                <td style="padding: 8px 0; font-weight: 600; color: #10b981; text-align: right;">€{{ number_format($investment->total_repayment, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Total Interest:</td>
                <td style="padding: 8px 0; font-weight: 600; color: #10b981; text-align: right;">€{{ number_format($investment->total_interest, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Start Date:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">{{ \Carbon\Carbon::parse($investment->start_date)->format('d.m.Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">End Date:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">{{ \Carbon\Carbon::parse($investment->end_date)->format('d.m.Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="alert-box info">
        <h4>What Happens Next?</h4>
        <ul style="margin: 8px 0 0 0; padding-left: 20px;">
            <li>Your repayment schedule has been generated</li>
            <li>You will receive repayments {{ $investment->repayment_interval }}</li>
            <li>You can track your investment progress in your dashboard</li>
            <li>Your investment contract will be available for download soon</li>
        </ul>
    </div>

    <div style="text-align: center;">
        <a href="{{ config('app.frontend_url') }}/my/investments/{{ $investment->id }}" class="btn-primary">
            View Investment Details
        </a>
    </div>

    <div class="message" style="margin-top: 30px;">
        <p>Thank you for investing in sustainable energy!</p>
        <p>If you have any questions, please don't hesitate to contact our support team.</p>
    </div>
@endsection
