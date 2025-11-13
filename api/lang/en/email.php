<?php

return [
    'welcome' => [
        'subject' => 'Welcome to Solar Investment Platform',
        'greeting' => 'Welcome, :name!',
        'body' => 'Thank you for joining our solar investment platform. We are excited to have you on board.',
        'next_steps' => 'Next Steps',
        'verify_email' => 'Please verify your email address to get started.',
        'complete_profile' => 'Complete your profile to start investing.',
        'browse_projects' => 'Browse available solar projects.',
        'footer' => 'If you have any questions, feel free to contact our support team.',
    ],

    'document' => [
        'verified_subject' => 'Document Verified Successfully',
        'rejected_subject' => 'Document Verification Failed',
        'greeting' => 'Hello, :name!',
        'verified_body' => 'Your document ":document_type" has been verified successfully.',
        'rejected_body' => 'Unfortunately, your document ":document_type" has been rejected.',
        'rejection_reason' => 'Reason: :reason',
        'next_action_verified' => 'You can now proceed with your investment application.',
        'next_action_rejected' => 'Please upload a new document that meets our requirements.',
        'view_documents' => 'View My Documents',
    ],

    'investment' => [
        'confirmation_subject' => 'Investment Confirmation',
        'greeting' => 'Hello, :name!',
        'confirmation_body' => 'Your investment has been confirmed successfully.',
        'investment_details' => 'Investment Details',
        'amount' => 'Amount',
        'solar_plant' => 'Solar Plant',
        'duration' => 'Duration',
        'interest_rate' => 'Interest Rate',
        'start_date' => 'Start Date',
        'expected_return' => 'Expected Return',
        'next_steps' => 'You will receive regular updates about your investment.',
        'view_investment' => 'View Investment Details',
    ],

    'repayment' => [
        'upcoming_subject' => 'Upcoming Repayment Due',
        'overdue_subject' => 'Overdue Repayment Notice',
        'final_notice_subject' => 'FINAL NOTICE: Overdue Repayment',
        'reminder_subject' => 'Repayment Reminder',
        'greeting' => 'Hello, :name!',
        'upcoming_body' => 'This is a reminder that your repayment is due soon.',
        'overdue_body' => 'Your repayment is now overdue. Please make payment as soon as possible.',
        'final_notice_body' => 'This is a final notice. Your repayment is significantly overdue and requires immediate attention.',
        'repayment_details' => 'Repayment Details',
        'amount_due' => 'Amount Due',
        'due_date' => 'Due Date',
        'investment_ref' => 'Investment Reference',
        'payment_instructions' => 'Payment Instructions',
        'make_payment' => 'Make Payment',
    ],

    'invoice' => [
        'subject' => 'Invoice :invoice_number',
        'greeting' => 'Hello, :name!',
        'body' => 'Please find attached your invoice.',
        'invoice_details' => 'Invoice Details',
        'invoice_number' => 'Invoice Number',
        'invoice_date' => 'Invoice Date',
        'due_date' => 'Due Date',
        'amount' => 'Total Amount',
        'payment_instructions' => 'Payment Instructions',
        'view_invoice' => 'View Invoice Online',
        'attached' => 'Invoice PDF is attached to this email.',
    ],

    'common' => [
        'button' => [
            'login' => 'Login to Dashboard',
            'view' => 'View Details',
            'contact_support' => 'Contact Support',
        ],
        'footer' => [
            'thanks' => 'Thank you for choosing our platform!',
            'questions' => 'If you have any questions, please contact us at :email',
            'unsubscribe' => 'Unsubscribe from these emails',
        ],
    ],
];
