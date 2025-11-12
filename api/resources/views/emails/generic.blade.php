@extends('emails.layouts.base')

@section('title', $subject ?? 'Swift Admin Notification')

@section('header', $header ?? 'Important Update')

@section('content')
    @if(isset($userName))
    <div class="greeting">
        Hello {{ $userName }},
    </div>
    @endif
    
    <div class="message">
        {!! $content !!}
    </div>
    
    @if(isset($actionUrl) && isset($actionText))
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $actionUrl }}" class="btn-primary" style="color: #ffffff; text-decoration: none;">
            {{ $actionText }}
        </a>
    </div>
    @endif
    
    @if(isset($additionalInfo))
    <div class="info-section">
        {!! $additionalInfo !!}
    </div>
    @endif
    
    @if(isset($warningMessage))
    <div class="alert-box warning">
        {!! $warningMessage !!}
    </div>
    @endif
@endsection
