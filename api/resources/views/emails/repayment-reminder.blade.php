@extends('emails.layout')

@section('title', __('email.repayment.' . $reminderType . '_subject', [], $locale))

@section('content')
<h1>{{ __('email.repayment.greeting', ['name' => $user->firstname ?? $user->name], $locale) }}</h1>

<div class="content">
    @if($reminderType === 'upcoming')
        <div class="alert alert-warning">
            <strong>⏰</strong> {{ __('email.repayment.upcoming_body', [], $locale) }}
        </div>
    @elseif($reminderType === 'overdue')
        <div class="alert alert-danger">
            <strong>⚠️</strong> {{ __('email.repayment.overdue_body', [], $locale) }}
        </div>
    @elseif($reminderType === 'final_notice')
        <div class="alert alert-danger">
            <strong>🚨</strong> {{ __('email.repayment.final_notice_body', [], $locale) }}
        </div>
    @else
        <p>{{ __('email.repayment.reminder_body', [], $locale) }}</p>
    @endif

    <div class="details-box">
        <h3>{{ __('email.repayment.repayment_details', [], $locale) }}</h3>

        <div class="details-row">
            <span class="details-label">{{ __('email.repayment.amount', [], $locale) }}:</span>
            <span class="details-value"><strong>€{{ number_format($repayment->amount, 2) }}</strong></span>
        </div>

        <div class="details-row">
            <span class="details-label">{{ __('email.repayment.due_date', [], $locale) }}:</span>
            <span class="details-value">{{ $repayment->due_date->format('Y-m-d') }}</span>
        </div>

        @if($repayment->investment)
        <div class="details-row">
            <span class="details-label">{{ __('email.repayment.investment_id', [], $locale) }}:</span>
            <span class="details-value">{{ $repayment->investment->id }}</span>
        </div>
        @endif

        <div class="details-row">
            <span class="details-label">{{ __('email.repayment.status', [], $locale) }}:</span>
            <span class="details-value">{{ ucfirst($repayment->status) }}</span>
        </div>

        @if($repayment->status === 'overdue')
        <div class="details-row">
            <span class="details-label">{{ __('email.repayment.days_overdue', [], $locale) }}:</span>
            <span class="details-value"><strong style="color: #d9534f;">{{ $repayment->due_date->diffInDays(now()) }} {{ __('general.days', [], $locale) }}</strong></span>
        </div>
        @endif
    </div>

    @if($reminderType === 'final_notice')
        <div class="alert alert-warning">
            {{ __('email.repayment.final_notice_warning', [], $locale) }}
        </div>
    @endif

    <center>
        <a href="{{ config('app.frontend_url') }}/repayments/{{ $repayment->id }}" class="button">
            {{ __('email.repayment.make_payment', [], $locale) }}
        </a>
    </center>

    <p><small>{{ __('email.repayment.footer', [], $locale) }}</small></p>
</div>
@endsection
