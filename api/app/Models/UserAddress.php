<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'street',
        'street_number',
        'city',
        'postal_code',
        'country',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get full address as a string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            trim("{$this->street} {$this->street_number}"),
            $this->postal_code . ' ' . $this->city,
            $this->country,
        ]);

        return implode(', ', $parts);
    }
}
