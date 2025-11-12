@extends('emails.layouts.base')

@section('title', 'Welcome to Swift Admin')

@section('header', 'Welcome Aboard! 🎉')

@section('content')
    <div class="greeting">
        Hello {{ $userName }},
    </div>
    
    <div class="message">
        <strong>Congratulations!</strong> Your Swift Admin account has been successfully created. You're now part of our growing community of users who are transforming their workflow with our powerful admin tools.
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $confirmationLink }}" class="btn-primary" style="color: #ffffff; text-decoration: none;">
            Get Started
        </a>
    </div>
    
    <div class="info-section">
        <h3>What's Next?</h3>
        <ol>
            <li><strong>Complete your profile</strong> - Add your details and preferences</li>
            <li><strong>Explore the dashboard</strong> - Discover powerful analytics and insights</li>
            <li><strong>Connect your data</strong> - Import your existing data seamlessly</li>
            <li><strong>Customize your workspace</strong> - Make Swift Admin work for you</li>
        </ol>
    </div>
    
    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 12px; padding: 20px; margin: 30px 0; text-align: center;">
        <h3 style="color: #059669; margin: 0 0 10px 0;">Your Account Details</h3>
        <p style="color: #4b5563; margin: 5px 0;">
            <strong>Email:</strong> {{ $userEmail ?? 'your-email@example.com' }}
        </p>
        <p style="color: #4b5563; margin: 5px 0;">
            <strong>Account Type:</strong> {{ $accountType ?? 'Standard' }}
        </p>
    </div>
    
    <div class="alert-box info">
        <h4>Need Help?</h4>
        <p>Our support team is here to help you get the most out of Swift Admin. Visit our help center or contact support anytime.</p>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <p style="color: #6b7280; font-size: 14px;">
            Thank you for choosing Swift Admin. We're excited to be part of your journey!
        </p>
    </div>
@endsection
