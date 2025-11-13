<?php

namespace App\Mail;

use App\Models\InvestmentRepayment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RepaymentReminderEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public InvestmentRepayment $repayment;
    public string $reminderType;
    public string $locale;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, InvestmentRepayment $repayment, string $reminderType = 'upcoming', string $locale = 'en')
    {
        $this->user = $user;
        $this->repayment = $repayment;
        $this->reminderType = $reminderType;
        $this->locale = $locale;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjectKey = match($this->reminderType) {
            'upcoming' => 'email.repayment.upcoming_subject',
            'overdue' => 'email.repayment.overdue_subject',
            'final_notice' => 'email.repayment.final_notice_subject',
            default => 'email.repayment.reminder_subject',
        };

        return new Envelope(
            subject: __($subjectKey, [], $this->locale),
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
                'user' => $this->user,
                'repayment' => $this->repayment,
                'investment' => $this->repayment->investment,
                'reminderType' => $this->reminderType,
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
