@extends('emails.layout')

@section('title', $status === 'verified' ? __('email.document.verified_subject', [], $locale) : __('email.document.rejected_subject', [], $locale))

@section('content')
<h1>{{ __('email.document.greeting', ['name' => $user->firstname ?? $user->name], $locale) }}</h1>

<div class="content">
    @if($status === 'verified')
        <div class="alert alert-success">
            <strong>✓</strong> {{ __('email.document.verified_body', ['document_type' => $file->document_type], $locale) }}
        </div>

        <p>{{ __('email.document.next_action_verified', [], $locale) }}</p>
    @else
        <div class="alert alert-danger">
            <strong>✗</strong> {{ __('email.document.rejected_body', ['document_type' => $file->document_type], $locale) }}
        </div>

        @if($rejectionReason)
            <div class="details-box">
                <strong>{{ __('email.document.rejection_reason', ['reason' => $rejectionReason], $locale) }}</strong>
            </div>
        @endif

        <p>{{ __('email.document.next_action_rejected', [], $locale) }}</p>
    @endif

    <center>
        <a href="{{ config('app.frontend_url') }}/documents" class="button">
            {{ __('email.document.view_documents', [], $locale) }}
        </a>
    </center>
</div>
@endsection
