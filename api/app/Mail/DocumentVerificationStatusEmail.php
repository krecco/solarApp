<?php

namespace App\Mail;

use App\Models\File;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentVerificationStatusEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public File $file;
    public string $status;
    public ?string $rejectionReason;
    public string $locale;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, File $file, string $status, ?string $rejectionReason = null, string $locale = 'en')
    {
        $this->user = $user;
        $this->file = $file;
        $this->status = $status;
        $this->rejectionReason = $rejectionReason;
        $this->locale = $locale;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->status === 'verified'
            ? __('email.document.verified_subject', [], $this->locale)
            : __('email.document.rejected_subject', [], $this->locale);

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.document-verification',
            with: [
                'user' => $this->user,
                'file' => $this->file,
                'status' => $this->status,
                'rejectionReason' => $this->rejectionReason,
                'locale' => $this->locale,
            ],
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
