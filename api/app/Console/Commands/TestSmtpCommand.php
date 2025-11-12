<?php

namespace App\Console\Commands;

use App\Services\MailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestSmtpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test 
                            {email? : The recipient email address} 
                            {--type=all : Type of test to run (all, connection, simple, otp, raw)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SMTP configuration and send test emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipientEmail = $this->argument('email') ?? 'test@example.com';
        $testType = $this->option('type');
        
        $this->info('===========================================');
        $this->info('       SMTP Configuration Test');
        $this->info('===========================================');
        $this->newLine();
        
        // Display current configuration
        $this->info('Current SMTP Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Host', config('mail.mailers.smtp.host')],
                ['Port', config('mail.mailers.smtp.port')],
                ['Username', config('mail.mailers.smtp.username')],
                ['From Address', config('mail.from.address')],
                ['From Name', config('mail.from.name')],
                ['Encryption', config('mail.mailers.smtp.encryption') ?? 'none'],
            ]
        );
        
        $mailService = new MailService();
        
        // Test connection
        if (in_array($testType, ['all', 'connection'])) {
            $this->testConnection($mailService);
        }
        
        // Send simple test email
        if (in_array($testType, ['all', 'simple'])) {
            $this->sendSimpleEmail($mailService, $recipientEmail);
        }
        
        // Send OTP test email
        if (in_array($testType, ['all', 'otp'])) {
            $this->sendOtpEmail($mailService, $recipientEmail);
        }
        
        // Send raw email
        if (in_array($testType, ['all', 'raw'])) {
            $this->sendRawEmail($recipientEmail);
        }
        
        $this->newLine();
        $this->info('===========================================');
        $this->info('              Test Summary');
        $this->info('===========================================');
        $this->info("All tests completed. Please check the inbox for: $recipientEmail");
        $this->newLine();
        $this->info('If emails are not received, please check:');
        $this->info('1. Spam/Junk folder');
        $this->info('2. SMTP credentials in .env file');
        $this->info('3. Firewall settings for port ' . config('mail.mailers.smtp.port'));
        $this->info('4. Laravel logs at storage/logs/laravel.log');
        
        return Command::SUCCESS;
    }
    
    /**
     * Test SMTP connection
     */
    private function testConnection(MailService $mailService): void
    {
        $this->newLine();
        $this->info('Test 1: Testing SMTP Connection...');
        $this->info('----------------------------');
        
        $connectionTest = $mailService->testSmtpConnection();
        
        if ($connectionTest['success']) {
            $this->info('✅ ' . $connectionTest['message']);
            
            if (isset($connectionTest['config'])) {
                $this->table(
                    ['Config', 'Value'],
                    collect($connectionTest['config'])->map(function ($value, $key) {
                        return [ucfirst($key), $value];
                    })->toArray()
                );
            }
        } else {
            $this->error('❌ ' . $connectionTest['message']);
            $this->error('Please check your SMTP settings in .env file');
        }
    }
    
    /**
     * Send simple test email
     */
    private function sendSimpleEmail(MailService $mailService, string $recipientEmail): void
    {
        $this->newLine();
        $this->info("Test 2: Sending test email to: $recipientEmail");
        $this->info('----------------------------');
        
        try {
            $result = $mailService->sendTestEmail($recipientEmail);
            
            if ($result) {
                $this->info('✅ Test email sent successfully!');
                $this->info("Please check the inbox for: $recipientEmail");
            } else {
                $this->error('❌ Failed to send test email. Check logs for details.');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error sending email: ' . $e->getMessage());
        }
    }
    
    /**
     * Send OTP test email
     */
    private function sendOtpEmail(MailService $mailService, string $recipientEmail): void
    {
        $this->newLine();
        $this->info('Test 3: Sending OTP test email...');
        $this->info('----------------------------');
        
        try {
            $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $result = $mailService->sendOtpEmail($recipientEmail, 'Test User', $otpCode);
            
            if ($result) {
                $this->info('✅ OTP email sent successfully!');
                $this->info("OTP Code: $otpCode");
            } else {
                $this->error('❌ Failed to send OTP email. Check logs for details.');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error sending OTP email: ' . $e->getMessage());
        }
    }
    
    /**
     * Send raw email using Mail facade
     */
    private function sendRawEmail(string $recipientEmail): void
    {
        $this->newLine();
        $this->info('Test 4: Sending raw email using Mail facade...');
        $this->info('----------------------------');
        
        try {
            Mail::raw('This is a raw test email sent directly using Laravel Mail facade.', function ($message) use ($recipientEmail) {
                $message->to($recipientEmail)
                        ->subject('Raw Email Test - Laravel Mail Facade');
            });
            
            $this->info('✅ Raw email sent successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Error sending raw email: ' . $e->getMessage());
        }
    }
}
