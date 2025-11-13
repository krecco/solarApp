<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'type',
        'attachments',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the conversation this message belongs to.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the sender of the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Scope to get unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get read messages.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope to get messages by sender.
     */
    public function scopeBySender($query, $senderId)
    {
        return $query->where('sender_id', $senderId);
    }

    /**
     * Scope to get text messages.
     */
    public function scopeText($query)
    {
        return $query->where('type', 'text');
    }

    /**
     * Scope to get system messages.
     */
    public function scopeSystem($query)
    {
        return $query->where('type', 'system');
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Check if message has attachments.
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * Check if message is from system.
     */
    public function isSystemMessage(): bool
    {
        return $this->type === 'system';
    }

    /**
     * Check if message is from a specific user.
     */
    public function isFrom($userId): bool
    {
        return $this->sender_id === $userId;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Update conversation's last_message_at when a new message is created
        static::created(function ($message) {
            $message->conversation()->update([
                'last_message_at' => $message->created_at,
            ]);

            // Increment unread count for other participants
            $message->conversation->incrementUnreadCount($message->sender_id);
        });
    }
}
