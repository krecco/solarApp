<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSepaPermission extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'iban',
        'bic',
        'account_holder',
        'mandate_reference',
        'mandate_date',
        'is_active',
    ];

    protected $casts = [
        'mandate_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active mandates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
