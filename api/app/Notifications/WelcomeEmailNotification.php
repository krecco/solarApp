<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeEmailNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'Admin Panel');
        $appUrl = config('app.url', 'http://localhost:3000');

        return (new MailMessage)
            ->subject("Welcome to {$appName}!")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Welcome to {$appName}. Your account has been successfully created.")
            ->line("You can now log in to your account and start using our platform.")
            ->action('Go to Dashboard', $appUrl)
            ->line('If you have any questions, please don\'t hesitate to contact our support team.')
            ->line('Thank you for joining us!')
            ->salutation("Best regards,\nThe {$appName} Team");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
