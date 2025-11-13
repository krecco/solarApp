<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WebInfo extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'type',
        'is_published',
        'is_featured',
        'published_at',
        'author_id',
        'featured_image',
        'meta',
        'tags',
        'category',
        'view_count',
        'rs',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'meta' => 'array',
        'tags' => 'array',
        'view_count' => 'integer',
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($webInfo) {
            if (empty($webInfo->slug)) {
                $webInfo->slug = Str::slug($webInfo->title);
            }
        });

        static::updating(function ($webInfo) {
            if ($webInfo->isDirty('title') && empty($webInfo->slug)) {
                $webInfo->slug = Str::slug($webInfo->title);
            }
        });
    }

    /**
     * Get the author of the web info
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope to get published items only
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('rs', 0)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope to get featured items
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }
}
