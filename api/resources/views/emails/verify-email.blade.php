@extends('emails.layouts.base')

@section('title', 'Verify Your Email - Swift Admin')

@section('header', 'Welcome to Swift Admin!')

@section('content')
    <div class="greeting">
        Hello {{ $userName }},
    </div>
    
    <div class="message">
        Thank you for registering with Swift Admin! We're excited to have you on board.
    </div>
    
    <div class="message">
        To complete your registration and verify your email address, please use the verification code below:
    </div>
    
    <div class="code-box">
        <div class="code-label">Verification Code</div>
        <div class="code">{{ $verificationCode }}</div>
    </div>
    
    <div class="info-section">
        <h3>How to verify:</h3>
        <ol>
            <li>Click the button below or go to the verification page</li>
            <li>Enter the 6-digit code shown above</li>
            <li>Click "Verify" to complete your registration</li>
        </ol>
    </div>
    
    <div class="button-container">
        <a href="{{ env('FRONTEND_WEB_URL', env('FRONTEND_URL', 'http://localhost:3000')) }}/auth/verify-email/pending" class="button button-primary">Verify Your Email</a>
    </div>
    
    <div class="alert-box warning">
        <h4>Important:</h4>
        <ul style="margin: 0; padding-left: 20px;">
            <li>This code will expire in 60 minutes</li>
            <li>If you didn't create an account, please ignore this email</li>
            <li>Never share this code with anyone</li>
        </ul>
    </div>
@endsection
