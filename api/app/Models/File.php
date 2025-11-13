<?php

namespace App\Models;

use App\Enums\DocumentType;
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
        'document_type',
        'is_required',
        'is_verified',
        'verified_at',
        'verified_by_id',
        'rejection_reason',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_required' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'document_type' => DocumentType::class,
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

    /**
     * Get the document type enum instance
     */
    public function getDocumentTypeEnum(): ?DocumentType
    {
        if (!$this->document_type) {
            return null;
        }

        if ($this->document_type instanceof DocumentType) {
            return $this->document_type;
        }

        return DocumentType::tryFrom($this->document_type);
    }

    /**
     * Check if this file is a verified required document
     */
    public function isVerifiedRequiredDocument(): bool
    {
        return $this->is_required && $this->is_verified;
    }

    /**
     * Check if this file is pending verification
     */
    public function isPendingVerification(): bool
    {
        return $this->is_required && !$this->is_verified && !$this->rejection_reason;
    }

    /**
     * Check if this file was rejected
     */
    public function isRejected(): bool
    {
        return !$this->is_verified && !empty($this->rejection_reason);
    }

    /**
     * Scope to filter by document type
     */
    public function scopeOfDocumentType($query, DocumentType|string $documentType)
    {
        $value = $documentType instanceof DocumentType ? $documentType->value : $documentType;
        return $query->where('document_type', $value);
    }

    /**
     * Scope to get only required documents
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope to get verified documents
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to get pending verification documents
     */
    public function scopePendingVerification($query)
    {
        return $query->where('is_required', true)
            ->where('is_verified', false)
            ->whereNull('rejection_reason');
    }

    /**
     * Scope to get rejected documents
     */
    public function scopeRejected($query)
    {
        return $query->where('is_verified', false)
            ->whereNotNull('rejection_reason');
    }
}
