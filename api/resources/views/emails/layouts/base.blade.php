<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Swift Admin')</title>
    <style>
        /* Reset and Base Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background-color: #f0f4f3;
        }
        
        /* Container */
        .email-wrapper {
            background-color: #f0f4f3;
            padding: 40px 20px;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            overflow: hidden;
        }
        
        /* Header with green gradient pattern */
        .email-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Subtle pattern overlay like in the app */
        .email-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255, 255, 255, 0.05) 10px,
                rgba(255, 255, 255, 0.05) 20px
            );
            pointer-events: none;
        }
        
        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        /* Logo/Icon */
        .logo-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }
        
        .logo-icon span {
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
        }
        
        /* Content Area */
        .email-content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            color: #1a1a1a;
            margin-bottom: 20px;
        }
        
        .message {
            color: #4b5563;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        /* Code/Token Box - Green themed */
        .code-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin: 30px 0;
            position: relative;
        }
        
        .code-box .code {
            font-size: 36px;
            font-weight: 700;
            color: #059669;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        
        .code-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        /* Button Styles - Matching the app */
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        
        /* Info Sections */
        .info-section {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .info-section h3 {
            color: #1a1a1a;
            font-size: 16px;
            margin: 0 0 12px 0;
            font-weight: 600;
        }
        
        .info-section ul, .info-section ol {
            margin: 0;
            padding-left: 20px;
            color: #4b5563;
        }
        
        .info-section li {
            margin-bottom: 8px;
        }
        
        /* Alert/Important Box */
        .alert-box {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .alert-box.warning {
            background: #fefce8;
            border-left-color: #eab308;
        }
        
        .alert-box.info {
            background: #eff6ff;
            border-left-color: #3b82f6;
        }
        
        .alert-box h4 {
            margin: 0 0 8px 0;
            color: #1a1a1a;
            font-size: 14px;
            font-weight: 600;
        }
        
        .alert-box p, .alert-box ul {
            margin: 0;
            color: #4b5563;
            font-size: 14px;
        }
        
        /* Footer */
        .email-footer {
            background: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .email-footer p {
            color: #6b7280;
            font-size: 14px;
            margin: 8px 0;
        }
        
        .email-footer .copyright {
            color: #9ca3af;
            font-size: 13px;
            margin-top: 20px;
        }
        
        /* Social Links */
        .social-links {
            margin: 20px 0;
        }
        
        .social-links a {
            display: inline-block;
            width: 32px;
            height: 32px;
            background: #e5e7eb;
            border-radius: 50%;
            margin: 0 6px;
            text-decoration: none;
            line-height: 32px;
            color: #6b7280;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: #10b981;
            color: #ffffff;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                border-radius: 0;
            }
            
            .email-header {
                padding: 30px 20px;
            }
            
            .email-content {
                padding: 30px 20px;
            }
            
            .code-box .code {
                font-size: 28px;
                letter-spacing: 4px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="logo-icon">
                    <span>⚡</span>
                </div>
                <h1>@yield('header', 'Swift Admin')</h1>
            </div>
            
            <!-- Content -->
            <div class="email-content">
                @yield('content')
            </div>
            
            <!-- Footer -->
            <div class="email-footer">
                <p>This is an automated message, please do not reply to this email.</p>
                <p class="copyright">© {{ date('Y') }} Swift Admin. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
