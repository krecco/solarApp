@extends('emails.layouts.base')

@section('title', 'Repayment Reminder')

@section('header', 'Solar Planning - Repayment Reminder')

@section('content')
    <div class="greeting">
        Hello {{ $user->name }},
    </div>

    <div class="message">
        @if($daysUntilDue > 0)
            <p>This is a friendly reminder that your investment repayment is due in <strong>{{ $daysUntilDue }} day{{ $daysUntilDue > 1 ? 's' : '' }}</strong>.</p>
        @else
            <p>This is a reminder that your investment repayment is <strong>due today</strong>.</p>
        @endif
    </div>

    <div class="code-box" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-color: #f59e0b;">
        <div class="code-label" style="color: #92400e;">Amount Due</div>
        <div class="code" style="color: #f59e0b; letter-spacing: normal; font-size: 32px;">
            €{{ number_format($repayment->amount, 2, ',', '.') }}
        </div>
    </div>

    <div class="info-section">
        <h3>Payment Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Due Date:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">{{ \Carbon\Carbon::parse($repayment->due_date)->format('d.m.Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Payment Number:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">{{ $repayment->payment_number }} of {{ $repayment->total_payments }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Principal Amount:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">€{{ number_format($repayment->principal_amount, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Interest Amount:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">€{{ number_format($repayment->interest_amount, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="info-section" style="background: #f0f9ff; border: 1px solid #bae6fd;">
        <h3>Investment Information</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Solar Plant:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">{{ $investment->solarPlant->title }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Total Investment:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">€{{ number_format($investment->amount, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Repayment Interval:</td>
                <td style="padding: 8px 0; font-weight: 600; text-align: right;">{{ ucfirst($investment->repayment_interval) }}</td>
            </tr>
        </table>
    </div>

    <div class="alert-box info">
        <h4>Payment Methods</h4>
        <p>Repayments will be automatically processed according to the payment method on file. If you have any questions about your payment, please contact our support team.</p>
    </div>

    <div style="text-align: center;">
        <a href="{{ config('app.frontend_url') }}/my/investments/{{ $investment->id }}" class="btn-primary">
            View Investment Details
        </a>
    </div>

    <div class="message" style="margin-top: 30px;">
        <p>Thank you for your continued investment in sustainable energy!</p>
        <p style="font-size: 14px; color: #6b7280;">If you have any questions or concerns, please don't hesitate to contact our support team.</p>
    </div>
@endsection
