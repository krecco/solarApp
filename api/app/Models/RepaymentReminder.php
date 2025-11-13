<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepaymentReminder extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'repayment_id',
        'user_id',
        'type',
        'days_before_due',
        'days_overdue',
        'sent_at',
        'sent_via',
        'recipient_email',
        'was_opened',
        'opened_at',
        'message_content',
        'metadata',
        'rs',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'was_opened' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the repayment associated with the reminder
     */
    public function repayment(): BelongsTo
    {
        return $this->belongsTo(InvestmentRepayment::class, 'repayment_id');
    }

    /**
     * Get the user that received the reminder
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark reminder as opened
     */
    public function markAsOpened(): void
    {
        if (!$this->was_opened) {
            $this->update([
                'was_opened' => true,
                'opened_at' => now(),
            ]);
        }
    }

    /**
     * Scope to get sent reminders
     */
    public function scopeSent($query)
    {
        return $query->whereNotNull('sent_at')
            ->where('rs', 0);
    }

    /**
     * Scope to get opened reminders
     */
    public function scopeOpened($query)
    {
        return $query->where('was_opened', true)
            ->where('rs', 0);
    }

    /**
     * Scope to get reminders by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
