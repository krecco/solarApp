<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OtpMail;
use App\Mail\ForgotPasswordMail;
use App\Mail\RegistrationConfirmationMail;
use App\Mail\VerifyEmailMail;
use App\Mail\GenericMail;

class MailService
{
    /**
     * Get backup email address from environment
     */
    protected function getBackupEmail(): ?string
    {
        $backupEmail = env('FRONTEND_WEB_MAIL_BACKUP');
        return filter_var($backupEmail, FILTER_VALIDATE_EMAIL) ? $backupEmail : null;
    }
    /**
     * Send OTP email
     */
    public function sendOtpEmail(string $email, string $name, string $otpCode): bool
    {
        try {
            $mail = Mail::to($email);
            
            // Add BCC for backup if configured
            if ($backupEmail = $this->getBackupEmail()) {
                $mail->bcc($backupEmail);
            }
            
            $mail->send(new OtpMail($name, $otpCode));
            
            Log::info('OTP email sent successfully', [
                'email' => $email,
                'bcc_backup' => $backupEmail ?? 'none',
                'timestamp' => now()->toDateTimeString()
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(string $email, string $name, string $resetLink): bool
    {
        try {
            $mail = Mail::to($email);
            
            // Add BCC for backup if configured
            if ($backupEmail = $this->getBackupEmail()) {
                $mail->bcc($backupEmail);
            }
            
            $mail->send(new ForgotPasswordMail($name, $resetLink));
            
            Log::info('Password reset email sent successfully', [
                'email' => $email,
                'bcc_backup' => $backupEmail ?? 'none',
                'timestamp' => now()->toDateTimeString()
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Send registration confirmation email
     */
    public function sendRegistrationConfirmationEmail(string $email, string $name, string $confirmationLink): bool
    {
        try {
            $mail = Mail::to($email);
            
            // Add BCC for backup if configured
            if ($backupEmail = $this->getBackupEmail()) {
                $mail->bcc($backupEmail);
            }
            
            $mail->send(new RegistrationConfirmationMail($name, $confirmationLink));
            
            Log::info('Registration confirmation email sent successfully', [
                'email' => $email,
                'bcc_backup' => $backupEmail ?? 'none',
                'timestamp' => now()->toDateTimeString()
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send registration confirmation email', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Send email verification code
     */
    public function sendVerificationEmail(string $email, string $name, string $verificationCode): bool
    {
        try {
            $mail = Mail::to($email);
            
            // Add BCC for backup if configured
            if ($backupEmail = $this->getBackupEmail()) {
                $mail->bcc($backupEmail);
            }
            
            $mail->send(new VerifyEmailMail($name, $verificationCode));
            
            Log::info('Verification email sent successfully', [
                'email' => $email,
                'bcc_backup' => $backupEmail ?? 'none',
                'timestamp' => now()->toDateTimeString()
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send verification email', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Send generic email
     */
    public function sendGenericEmail(
        string $email, 
        string $subject, 
        string $content, 
        array $cc = [], 
        array $bcc = [],
        array $attachments = []
    ): bool {
        try {
            $mail = Mail::to($email);
            
            if (!empty($cc)) {
                $mail->cc($cc);
            }
            
            // Add user-provided BCC
            if (!empty($bcc)) {
                $mail->bcc($bcc);
            }
            
            // Add backup BCC if configured
            if ($backupEmail = $this->getBackupEmail()) {
                // Merge with existing BCC if any
                $allBcc = array_merge($bcc, [$backupEmail]);
                $mail->bcc(array_unique($allBcc));
            }
            
            $mailMessage = new GenericMail($subject, $content);
            
            // Add attachments if provided
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $mailMessage->attach($attachment);
                }
            }
            
            $mail->send($mailMessage);
            
            Log::info('Generic email sent successfully', [
                'email' => $email,
                'subject' => $subject,
                'bcc_backup' => $backupEmail ?? 'none',
                'timestamp' => now()->toDateTimeString()
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send generic email', [
                'email' => $email,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Send raw email with custom headers
     */
    public function sendRawEmail(array $to, array $data): bool
    {
        try {
            $backupEmail = $this->getBackupEmail();
            
            Mail::raw($data['body'], function ($message) use ($to, $data, $backupEmail) {
                $message->to($to['email'], $to['name'] ?? null)
                        ->subject($data['subject']);
                
                // Add backup BCC if configured
                if ($backupEmail) {
                    $message->bcc($backupEmail);
                }
                
                // Add custom headers if provided
                if (isset($data['headers']) && is_array($data['headers'])) {
                    foreach ($data['headers'] as $key => $value) {
                        $message->getHeaders()->addTextHeader($key, $value);
                    }
                }
                
                // Set priority if provided
                if (isset($data['priority'])) {
                    $message->priority($data['priority']);
                }
            });
            
            Log::info('Raw email sent successfully', [
                'email' => $to['email'],
                'subject' => $data['subject'],
                'bcc_backup' => $backupEmail ?? 'none',
                'timestamp' => now()->toDateTimeString()
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send raw email', [
                'email' => $to['email'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Test SMTP connection
     */
    public function testSmtpConnection(): array
    {
        try {
            $config = config('mail.mailers.smtp');
            
            $connection = @fsockopen(
                $config['host'],
                $config['port'],
                $errno,
                $errstr,
                5
            );
            
            if (!$connection) {
                return [
                    'success' => false,
                    'message' => "Failed to connect to SMTP server: {$errstr} (Error {$errno})",
                    'config' => [
                        'host' => $config['host'],
                        'port' => $config['port'],
                        'encryption' => $config['encryption'] ?? 'none'
                    ]
                ];
            }
            
            fclose($connection);
            
            return [
                'success' => true,
                'message' => 'SMTP connection successful',
                'config' => [
                    'host' => $config['host'],
                    'port' => $config['port'],
                    'encryption' => $config['encryption'] ?? 'none',
                    'username' => $config['username'] ?? 'not set'
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'SMTP connection test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send test email
     */
    public function sendTestEmail(string $email): bool
    {
        try {
            $testData = [
                'timestamp' => now()->toDateTimeString(),
                'smtp_host' => config('mail.mailers.smtp.host'),
                'smtp_port' => config('mail.mailers.smtp.port'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name')
            ];
            
            $backupEmail = $this->getBackupEmail();
            
            Mail::raw("This is a test email from your Laravel application.\n\nConfiguration:\n" . json_encode($testData, JSON_PRETTY_PRINT), function ($message) use ($email, $backupEmail) {
                $message->to($email)
                        ->subject('Test Email - SMTP Configuration Working');
                
                // Add backup BCC if configured
                if ($backupEmail) {
                    $message->bcc($backupEmail);
                }
            });
            
            Log::info('Test email sent successfully', [
                'email' => $email,
                'bcc_backup' => $backupEmail ?? 'none',
                'timestamp' => now()->toDateTimeString()
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
}
