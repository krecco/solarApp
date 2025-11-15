<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasUuids;

    protected $table = 'activities';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Use custom timestamp column
    const CREATED_AT = 't0';
    const UPDATED_AT = null;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'content_id',
        'parent_content_id',
        'content_type',
        'notification_type',
        'show_on_user_dashboard',
        'filename',
        'created_by',
        'created_by_id',
        'rs',
    ];

    protected $casts = [
        'show_on_user_dashboard' => 'boolean',
        't0' => 'datetime',
    ];

    /**
     * Get the user that this activity belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user that created this activity.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
