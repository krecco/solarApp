<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'customer_no',
        'customer_type',
        'is_business',
        'title_prefix',
        'title_suffix',
        'phone_nr',
        'gender',
        'user_files_verified',
        'user_verified_at',
        'document_extra_text_block_a',
        'document_extra_text_block_b',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_business' => 'boolean',
        'user_files_verified' => 'boolean',
        'user_verified_at' => 'datetime',
    ];

    /**
     * Get the user that owns the customer profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full title (prefix + name + suffix).
     */
    public function getFullTitleAttribute(): string
    {
        $parts = array_filter([
            $this->title_prefix,
            $this->user->name ?? '',
            $this->title_suffix,
        ]);

        return implode(' ', $parts);
    }
}
