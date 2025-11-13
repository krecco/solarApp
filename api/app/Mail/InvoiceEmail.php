<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public Invoice $invoice;
    public string $locale;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Invoice $invoice, string $locale = 'en')
    {
        $this->user = $user;
        $this->invoice = $invoice;
        $this->locale = $locale;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('email.invoice.subject', ['invoice_number' => $this->invoice->invoice_number], $this->locale),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'user' => $this->user,
                'invoice' => $this->invoice,
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
        $attachments = [];

        // Attach PDF if available
        if ($this->invoice->pdf_path && \Storage::disk('private')->exists($this->invoice->pdf_path)) {
            $attachments[] = Attachment::fromStorageDisk('private', $this->invoice->pdf_path)
                ->as('invoice-' . $this->invoice->invoice_number . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
