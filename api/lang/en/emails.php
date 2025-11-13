<?php

return [
    // Email subjects
    'welcome_subject' => 'Welcome to :app_name',
    'verification_subject' => 'Verify Your Email Address',
    'password_reset_subject' => 'Reset Your Password',
    'investment_confirmed_subject' => 'Investment Confirmed - :contract_number',
    'repayment_processed_subject' => 'Repayment Processed - :amount',
    'document_ready_subject' => 'Your Document is Ready',
    'new_message_subject' => 'You have a new message',
    'plant_status_subject' => 'Solar Plant Status Update - :plant_name',

    // Email greetings
    'greeting' => 'Hello',
    'greeting_with_name' => 'Hello :name',
    'regards' => 'Best regards',
    'team_signature' => 'The :app_name Team',

    // Welcome email
    'welcome_line1' => 'Welcome to :app_name! We\'re excited to have you on board.',
    'welcome_line2' => 'You can now access your account and manage your investments.',
    'welcome_action' => 'Get Started',

    // Email verification
    'verify_line1' => 'Please click the button below to verify your email address.',
    'verify_line2' => 'If you did not create an account, no further action is required.',
    'verify_action' => 'Verify Email Address',
    'verify_expire' => 'This verification link will expire in :count minutes.',

    // Password reset
    'reset_line1' => 'You are receiving this email because we received a password reset request for your account.',
    'reset_line2' => 'If you did not request a password reset, no further action is required.',
    'reset_action' => 'Reset Password',
    'reset_expire' => 'This password reset link will expire in :count minutes.',

    // Investment notifications
    'investment_confirmed_line1' => 'Your investment has been successfully confirmed.',
    'investment_confirmed_line2' => 'Amount: :amount',
    'investment_confirmed_line3' => 'Contract Number: :contract_number',
    'investment_confirmed_action' => 'View Investment Details',

    // Repayment notifications
    'repayment_processed_line1' => 'A repayment has been processed for your investment.',
    'repayment_processed_line2' => 'Amount: :amount',
    'repayment_processed_line3' => 'Date: :date',
    'repayment_processed_action' => 'View Repayment Details',

    // Document notifications
    'document_ready_line1' => 'Your requested document is now ready for download.',
    'document_ready_line2' => 'Document Type: :type',
    'document_ready_action' => 'Download Document',

    // Message notifications
    'new_message_line1' => 'You have received a new message.',
    'new_message_line2' => 'From: :from',
    'new_message_action' => 'View Message',

    // Plant status notifications
    'plant_status_line1' => 'Status update for your solar plant: :plant_name',
    'plant_status_line2' => 'Status: :status',
    'plant_status_action' => 'View Plant Details',

    // Common email elements
    'button_trouble' => 'If you\'re having trouble clicking the ":action" button, copy and paste the URL below into your web browser:',
    'unsubscribe' => 'If you no longer wish to receive these emails, you can',
    'unsubscribe_link' => 'unsubscribe here',
    'email_footer' => '© :year :app_name. All rights reserved.',
    'privacy_policy' => 'Privacy Policy',
    'terms_of_service' => 'Terms of Service',
    'contact_us' => 'Contact Us',
];
