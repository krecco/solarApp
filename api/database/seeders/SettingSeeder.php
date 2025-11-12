<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates default system settings
     */
    public function run(): void
    {
        $settings = $this->getDefaultSettings();

        foreach ($settings as $group => $groupSettings) {
            foreach ($groupSettings as $key => $config) {
                Setting::firstOrCreate(
                    [
                        'group' => $group,
                        'key' => $key,
                    ],
                    [
                        'value' => $config['value'],
                        'type' => $config['type'],
                        'description' => $config['description'] ?? null,
                        'is_public' => $config['is_public'] ?? false,
                    ]
                );
            }
        }

        $this->command->info('Default settings created successfully.');
    }

    /**
     * Get default settings configuration
     */
    protected function getDefaultSettings(): array
    {
        return [
            'general' => [
                'app_name' => [
                    'value' => 'Solar Planning',
                    'type' => 'string',
                    'description' => 'Application name',
                    'is_public' => true,
                ],
                'maintenance_mode' => [
                    'value' => false,
                    'type' => 'boolean',
                    'description' => 'Enable maintenance mode',
                    'is_public' => true,
                ],
                'default_language' => [
                    'value' => 'de',
                    'type' => 'string',
                    'description' => 'Default language (ISO 639-1)',
                    'is_public' => true,
                ],
                'default_currency' => [
                    'value' => 'EUR',
                    'type' => 'string',
                    'description' => 'Default currency (ISO 4217)',
                    'is_public' => true,
                ],
                'timezone' => [
                    'value' => 'Europe/Berlin',
                    'type' => 'string',
                    'description' => 'Application timezone',
                    'is_public' => true,
                ],
            ],
            'email' => [
                'from_address' => [
                    'value' => 'noreply@solarplanning.com',
                    'type' => 'string',
                    'description' => 'Default from email address',
                    'is_public' => false,
                ],
                'from_name' => [
                    'value' => 'Solar Planning',
                    'type' => 'string',
                    'description' => 'Default from name',
                    'is_public' => false,
                ],
                'admin_email' => [
                    'value' => 'admin@solarplanning.com',
                    'type' => 'string',
                    'description' => 'Administrator email for notifications',
                    'is_public' => false,
                ],
                'support_email' => [
                    'value' => 'support@solarplanning.com',
                    'type' => 'string',
                    'description' => 'Support contact email',
                    'is_public' => true,
                ],
            ],
            'investment' => [
                'min_investment_amount' => [
                    'value' => 500,
                    'type' => 'decimal',
                    'description' => 'Minimum investment amount in EUR',
                    'is_public' => true,
                ],
                'max_investment_amount' => [
                    'value' => 100000,
                    'type' => 'decimal',
                    'description' => 'Maximum investment amount in EUR',
                    'is_public' => true,
                ],
                'default_interest_rate' => [
                    'value' => 4.5,
                    'type' => 'decimal',
                    'description' => 'Default interest rate (%)',
                    'is_public' => true,
                ],
                'default_duration_months' => [
                    'value' => 120,
                    'type' => 'integer',
                    'description' => 'Default investment duration in months',
                    'is_public' => true,
                ],
                'auto_verify_investments' => [
                    'value' => false,
                    'type' => 'boolean',
                    'description' => 'Automatically verify new investments',
                    'is_public' => false,
                ],
                'require_contract_signing' => [
                    'value' => true,
                    'type' => 'boolean',
                    'description' => 'Require digital contract signing',
                    'is_public' => false,
                ],
            ],
            'payment' => [
                'payment_provider' => [
                    'value' => 'sepa',
                    'type' => 'string',
                    'description' => 'Payment provider (sepa, stripe, paypal)',
                    'is_public' => false,
                ],
                'enable_instant_payments' => [
                    'value' => false,
                    'type' => 'boolean',
                    'description' => 'Enable instant payment processing',
                    'is_public' => false,
                ],
                'late_fee_percentage' => [
                    'value' => 5,
                    'type' => 'decimal',
                    'description' => 'Late fee percentage for overdue payments',
                    'is_public' => false,
                ],
            ],
            'notification' => [
                'send_welcome_email' => [
                    'value' => true,
                    'type' => 'boolean',
                    'description' => 'Send welcome email to new users',
                    'is_public' => false,
                ],
                'send_investment_confirmations' => [
                    'value' => true,
                    'type' => 'boolean',
                    'description' => 'Send investment confirmation emails',
                    'is_public' => false,
                ],
                'send_repayment_reminders' => [
                    'value' => true,
                    'type' => 'boolean',
                    'description' => 'Send repayment reminder emails',
                    'is_public' => false,
                ],
                'reminder_days_before_due' => [
                    'value' => 7,
                    'type' => 'integer',
                    'description' => 'Days before due date to send reminders',
                    'is_public' => false,
                ],
                'send_monthly_reports' => [
                    'value' => true,
                    'type' => 'boolean',
                    'description' => 'Send monthly investment reports',
                    'is_public' => false,
                ],
            ],
            'security' => [
                'require_email_verification' => [
                    'value' => true,
                    'type' => 'boolean',
                    'description' => 'Require email verification for new accounts',
                    'is_public' => false,
                ],
                'enable_2fa' => [
                    'value' => false,
                    'type' => 'boolean',
                    'description' => 'Enable two-factor authentication',
                    'is_public' => false,
                ],
                'session_lifetime' => [
                    'value' => 120,
                    'type' => 'integer',
                    'description' => 'Session lifetime in minutes',
                    'is_public' => false,
                ],
                'password_min_length' => [
                    'value' => 8,
                    'type' => 'integer',
                    'description' => 'Minimum password length',
                    'is_public' => true,
                ],
                'max_login_attempts' => [
                    'value' => 5,
                    'type' => 'integer',
                    'description' => 'Maximum login attempts before lockout',
                    'is_public' => false,
                ],
            ],
        ];
    }
}
