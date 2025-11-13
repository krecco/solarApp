@extends('emails.layout')

@section('title', __('email.invoice.subject', ['invoice_number' => $invoice->invoice_number], $locale))

@section('content')
<h1>{{ __('email.invoice.greeting', ['name' => $user->firstname ?? $user->name], $locale) }}</h1>

<div class="content">
    <p>{{ __('email.invoice.body', [], $locale) }}</p>

    <div class="details-box">
        <h3>{{ __('email.invoice.invoice_details', [], $locale) }}</h3>

        <div class="details-row">
            <span class="details-label">{{ __('email.invoice.invoice_number', [], $locale) }}:</span>
            <span class="details-value"><strong>{{ $invoice->invoice_number }}</strong></span>
        </div>

        <div class="details-row">
            <span class="details-label">{{ __('email.invoice.invoice_date', [], $locale) }}:</span>
            <span class="details-value">{{ $invoice->invoice_date->format('Y-m-d') }}</span>
        </div>

        <div class="details-row">
            <span class="details-label">{{ __('email.invoice.due_date', [], $locale) }}:</span>
            <span class="details-value">{{ $invoice->due_date->format('Y-m-d') }}</span>
        </div>

        <div class="details-row">
            <span class="details-label">{{ __('email.invoice.amount', [], $locale) }}:</span>
            <span class="details-value"><strong>{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</strong></span>
        </div>
    </div>

    @if($invoice->pdf_path)
        <div class="alert alert-success">
            ✓ {{ __('email.invoice.attached', [], $locale) }}
        </div>
    @endif

    <center>
        <a href="{{ config('app.frontend_url') }}/invoices/{{ $invoice->id }}" class="button">
            {{ __('email.invoice.view_invoice', [], $locale) }}
        </a>
    </center>
</div>
@endsection
