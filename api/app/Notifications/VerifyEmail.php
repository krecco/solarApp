<?php

namespace App\Notifications;

use App\Services\MailService;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class VerifyEmail extends BaseVerifyEmail
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Get existing code from cache, or generate a new one if not found
        $code = cache()->get('email_verification_code_' . $notifiable->id);
        
        if (!$code) {
            // Generate a 6-digit verification code
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store the code in cache for 60 minutes
            cache()->put('email_verification_code_' . $notifiable->id, $code, 3600);
        }
        
        // Log the verification code for testing (only in development)
        if (app()->environment('local', 'development')) {
            Log::info('Email Verification Code', [
                'user_id' => $notifiable->id,
                'email' => $notifiable->email,
                'code' => $code,
            ]);
        }
        
        // Send verification email using MailService
        $mailService = new MailService();
        $success = $mailService->sendVerificationEmail(
            $notifiable->email,
            $notifiable->name,
            $code
        );
        
        if (!$success) {
            Log::error('Failed to send verification email', [
                'user_id' => $notifiable->id,
                'email' => $notifiable->email,
            ]);
        }
        
        // Return a basic MailMessage to satisfy Laravel's notification system
        // (This won't actually be sent since we're using MailService above)
        return (new MailMessage)
            ->subject('Verify Your Email Address - ' . config('app.name'))
            ->line('Verification code: ' . $code);
    }
}
