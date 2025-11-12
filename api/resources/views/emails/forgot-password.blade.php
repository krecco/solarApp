@extends('emails.layouts.base')

@section('title', 'Reset Your Password - Swift Admin')

@section('header', 'Password Reset Request')

@section('content')
    <div class="greeting">
        Hello {{ $userName }},
    </div>
    
    <div class="message">
        We received a request to reset the password for your Swift Admin account. If you made this request, click the button below to create a new password:
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $resetLink }}" class="btn-primary" style="color: #ffffff; text-decoration: none;">
            Reset Password
        </a>
    </div>
    
    <div class="info-section">
        <h3>Can't click the button?</h3>
        <p style="word-break: break-all; color: #6b7280; font-size: 14px;">
            Copy and paste this link into your browser:<br>
            <a href="{{ $resetLink }}" style="color: #10b981;">{{ $resetLink }}</a>
        </p>
    </div>
    
    <div class="alert-box info">
        <h4>For your security:</h4>
        <ul style="margin: 0; padding-left: 20px;">
            <li>This link expires in 1 hour</li>
            <li>The link can only be used once</li>
            <li>If you didn't request a password reset, you can safely ignore this email</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <p style="color: #6b7280; font-size: 14px;">
            Your account security is important to us. If you have any concerns, please contact support.
        </p>
    </div>
@endsection
