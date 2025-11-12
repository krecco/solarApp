<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $userName;
    public string $confirmationLink;
    public ?string $userEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(string $userName, string $confirmationLink, ?string $userEmail = null)
    {
        $this->userName = $userName;
        $this->confirmationLink = $confirmationLink;
        $this->userEmail = $userEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . config('app.name') . ' - Please Confirm Your Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-confirmation',
            with: [
                'userName' => $this->userName,
                'confirmationLink' => $this->confirmationLink,
                'userEmail' => $this->userEmail,
                'appName' => config('app.name'),
                'accountType' => 'Standard',
                'expiryHours' => 48,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
