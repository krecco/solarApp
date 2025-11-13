<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'subject',
        'status',
        'created_by_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the user who created the conversation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Get all participants in the conversation.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['last_read_at', 'unread_count'])
            ->withTimestamps();
    }

    /**
     * Get all messages in the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the latest message in the conversation.
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Scope to get conversations for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }

    /**
     * Scope to get active conversations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get archived conversations.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope to order by last message time.
     */
    public function scopeLatestActivity($query)
    {
        return $query->orderByDesc('last_message_at');
    }

    /**
     * Get unread count for a specific user.
     */
    public function getUnreadCountForUser($userId): int
    {
        $participant = $this->participants()->where('users.id', $userId)->first();
        return $participant ? $participant->pivot->unread_count : 0;
    }

    /**
     * Mark conversation as read for a specific user.
     */
    public function markAsReadForUser($userId): void
    {
        $this->participants()->updateExistingPivot($userId, [
            'last_read_at' => now(),
            'unread_count' => 0,
        ]);
    }

    /**
     * Increment unread count for all participants except sender.
     */
    public function incrementUnreadCount($exceptUserId): void
    {
        $participantIds = $this->participants()
            ->where('users.id', '!=', $exceptUserId)
            ->pluck('users.id');

        foreach ($participantIds as $participantId) {
            $this->participants()->updateExistingPivot($participantId, [
                'unread_count' => \DB::raw('unread_count + 1'),
            ]);
        }
    }

    /**
     * Add a participant to the conversation.
     */
    public function addParticipant($userId): void
    {
        if (!$this->participants()->where('users.id', $userId)->exists()) {
            $this->participants()->attach($userId, [
                'last_read_at' => now(),
                'unread_count' => 0,
            ]);
        }
    }

    /**
     * Remove a participant from the conversation.
     */
    public function removeParticipant($userId): void
    {
        $this->participants()->detach($userId);
    }

    /**
     * Check if user is a participant.
     */
    public function hasParticipant($userId): bool
    {
        return $this->participants()->where('users.id', $userId)->exists();
    }

    /**
     * Get other participants (excluding the given user).
     */
    public function getOtherParticipants($userId)
    {
        return $this->participants()->where('users.id', '!=', $userId)->get();
    }

    /**
     * Archive the conversation.
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Reactivate the conversation.
     */
    public function reactivate(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Close the conversation.
     */
    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }
}
