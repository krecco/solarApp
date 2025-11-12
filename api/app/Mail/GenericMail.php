<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $mailSubject;
    public string $mailContent;
    public array $additionalData;
    protected array $mailAttachments = [];

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $content, array $additionalData = [])
    {
        $this->mailSubject = $subject;
        $this->mailContent = $content;
        $this->additionalData = $additionalData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.generic',
            with: array_merge([
                'subject' => $this->mailSubject,
                'content' => $this->mailContent,
                'appName' => config('app.name'),
            ], $this->additionalData)
        );
    }

    /**
     * Attach a file to the message.
     */
    public function attach($file, array $options = []): static
    {
        $this->mailAttachments[] = compact('file', 'options');
        return $this;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        foreach ($this->mailAttachments as $attachment) {
            if (file_exists($attachment['file'])) {
                $attachments[] = \Illuminate\Mail\Mailables\Attachment::fromPath($attachment['file']);
            }
        }
        
        return $attachments;
    }
}
