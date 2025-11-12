<?php

namespace App\Mail;

use App\Models\InvestmentRepayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RepaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public InvestmentRepayment $repayment;
    public int $daysUntilDue;

    /**
     * Create a new message instance.
     */
    public function __construct(InvestmentRepayment $repayment, int $daysUntilDue = 0)
    {
        $this->repayment = $repayment;
        $this->daysUntilDue = $daysUntilDue;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->daysUntilDue > 0
            ? "Repayment Due in {$this->daysUntilDue} Days"
            : "Repayment Due Today";

        return new Envelope(
            subject: $subject . ' - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.repayment-reminder',
            with: [
                'repayment' => $this->repayment,
                'investment' => $this->repayment->investment,
                'user' => $this->repayment->investment->user,
                'daysUntilDue' => $this->daysUntilDue,
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
