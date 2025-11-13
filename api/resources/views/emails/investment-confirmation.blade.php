@extends('emails.layout')

@section('title', __('email.investment.confirmation_subject', [], $locale))

@section('content')
<h1>{{ __('email.investment.greeting', ['name' => $user->firstname ?? $user->name], $locale) }}</h1>

<div class="content">
    <div class="alert alert-success">
        <strong>✓</strong> {{ __('email.investment.confirmation_body', [], $locale) }}
    </div>

    <div class="details-box">
        <h3>{{ __('email.investment.investment_details', [], $locale) }}</h3>

        <div class="details-row">
            <span class="details-label">{{ __('email.investment.amount', [], $locale) }}:</span>
            <span class="details-value">€{{ number_format($investment->amount, 2) }}</span>
        </div>

        <div class="details-row">
            <span class="details-label">{{ __('email.investment.solar_plant', [], $locale) }}:</span>
            <span class="details-value">{{ $solarPlant->title ?? 'N/A' }}</span>
        </div>

        <div class="details-row">
            <span class="details-label">{{ __('email.investment.duration', [], $locale) }}:</span>
            <span class="details-value">{{ $investment->duration_months }} {{ __('general.months', [], $locale) }}</span>
        </div>

        <div class="details-row">
            <span class="details-label">{{ __('email.investment.interest_rate', [], $locale) }}:</span>
            <span class="details-value">{{ $investment->interest_rate }}%</span>
        </div>

        @if($investment->start_date)
        <div class="details-row">
            <span class="details-label">{{ __('email.investment.start_date', [], $locale) }}:</span>
            <span class="details-value">{{ $investment->start_date->format('Y-m-d') }}</span>
        </div>
        @endif
    </div>

    <p>{{ __('email.investment.next_steps', [], $locale) }}</p>

    <center>
        <a href="{{ config('app.frontend_url') }}/investments/{{ $investment->id }}" class="button">
            {{ __('email.investment.view_investment', [], $locale) }}
        </a>
    </center>
</div>
@endsection
