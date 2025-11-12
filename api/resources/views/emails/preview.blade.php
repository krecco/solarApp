@extends('emails.layouts.base')

@section('title', 'Email Template Preview')

@section('header', 'Swift Admin Email Templates')

@section('content')
    <div class="greeting">
        Hello Administrator,
    </div>
    
    <div class="message">
        This is a preview of all email components and styles used in Swift Admin emails. These templates match the modern green design of your application.
    </div>
    
    <!-- Standard Code Box -->
    <div style="margin: 30px 0;">
        <h3 style="color: #1a1a1a; margin-bottom: 15px;">Verification Code Example:</h3>
        <div class="code-box">
            <div class="code-label">Verification Code</div>
            <div class="code">123456</div>
        </div>
    </div>
    
    <!-- Primary Button -->
    <div style="margin: 30px 0;">
        <h3 style="color: #1a1a1a; margin-bottom: 15px;">Primary Action Button:</h3>
        <div style="text-align: center;">
            <a href="#" class="btn-primary" style="color: #ffffff; text-decoration: none;">
                Take Action
            </a>
        </div>
    </div>
    
    <!-- Info Section -->
    <div style="margin: 30px 0;">
        <h3 style="color: #1a1a1a; margin-bottom: 15px;">Information Section:</h3>
        <div class="info-section">
            <h3>Quick Start Guide</h3>
            <ol>
                <li>First step with detailed instructions</li>
                <li>Second step to follow</li>
                <li>Final step to complete the process</li>
            </ol>
        </div>
    </div>
    
    <!-- Alert Boxes -->
    <div style="margin: 30px 0;">
        <h3 style="color: #1a1a1a; margin-bottom: 15px;">Alert Styles:</h3>
        
        <div class="alert-box">
            <h4>Error Alert</h4>
            <p>This is an error message that requires immediate attention.</p>
        </div>
        
        <div class="alert-box warning">
            <h4>Warning Alert</h4>
            <p>This is a warning message for important notices.</p>
        </div>
        
        <div class="alert-box info">
            <h4>Info Alert</h4>
            <p>This is an informational message for general updates.</p>
        </div>
    </div>
    
    <!-- Account Details Box -->
    <div style="margin: 30px 0;">
        <h3 style="color: #1a1a1a; margin-bottom: 15px;">Account Details Style:</h3>
        <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 12px; padding: 20px; text-align: center;">
            <h3 style="color: #059669; margin: 0 0 10px 0;">Your Account</h3>
            <p style="color: #4b5563; margin: 5px 0;">
                <strong>Email:</strong> user@example.com
            </p>
            <p style="color: #4b5563; margin: 5px 0;">
                <strong>Plan:</strong> Professional
            </p>
            <p style="color: #4b5563; margin: 5px 0;">
                <strong>Status:</strong> Active
            </p>
        </div>
    </div>
    
    <!-- Color Palette Reference -->
    <div style="margin: 30px 0;">
        <h3 style="color: #1a1a1a; margin-bottom: 15px;">Color Palette:</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: #10b981; border-radius: 8px;"></div>
                <small style="color: #6b7280;">Primary Green</small>
            </div>
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: #059669; border-radius: 8px;"></div>
                <small style="color: #6b7280;">Dark Green</small>
            </div>
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: #f0fdf4; border-radius: 8px; border: 1px solid #e5e7eb;"></div>
                <small style="color: #6b7280;">Light Green</small>
            </div>
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;"></div>
                <small style="color: #6b7280;">Gray Background</small>
            </div>
        </div>
    </div>
@endsection
