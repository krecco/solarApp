<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class File extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'file_container_id',
        'name',
        'original_name',
        'path',
        'mime_type',
        'size',
        'extension',
        'uploaded_by_type',
        'uploaded_by_id',
        'is_verified',
        'verified_at',
        'verified_by_id',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the file container.
     */
    public function fileContainer(): BelongsTo
    {
        return $this->belongsTo(FileContainer::class);
    }

    /**
     * Get the uploader (polymorphic).
     */
    public function uploadedBy(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the verifier.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
