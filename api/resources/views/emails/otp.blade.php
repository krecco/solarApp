@extends('emails.layouts.base')

@section('title', 'Your Login Code - Swift Admin')

@section('header', 'Secure Login')

@section('content')
    <div class="greeting">
        Hello {{ $userName }},
    </div>
    
    <div class="message">
        You've requested to log in to your Swift Admin account. Use the code below to complete your sign-in:
    </div>
    
    <div class="code-box">
        <div class="code-label">One-Time Password</div>
        <div class="code">{{ $otpCode }}</div>
    </div>
    
    <div class="info-section">
        <h3>How to use this code:</h3>
        <ol>
            <li>Return to the login page</li>
            <li>Enter this 6-digit code</li>
            <li>Click "Verify" to access your account</li>
        </ol>
    </div>
    
    <div class="alert-box warning">
        <h4>Security Notice:</h4>
        <ul style="margin: 0; padding-left: 20px;">
            <li>This code expires in 10 minutes</li>
            <li>Never share this code with anyone</li>
            <li>If you didn't request this code, please secure your account immediately</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <p style="color: #6b7280; font-size: 14px;">
            Having trouble? Contact our support team for assistance.
        </p>
    </div>
@endsection
