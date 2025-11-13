<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Campaign extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'type',
        'start_date',
        'end_date',
        'bonus_amount',
        'min_investment_amount',
        'max_uses',
        'current_uses',
        'is_active',
        'conditions',
        'terms',
        'code',
        'rs',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'bonus_amount' => 'decimal:2',
        'min_investment_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'conditions' => 'array',
        'max_uses' => 'integer',
        'current_uses' => 'integer',
    ];

    /**
     * Check if campaign is currently active and valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active || $this->rs !== 0) {
            return false;
        }

        $now = now();

        // Check start date
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        // Check end date
        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        // Check max uses
        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Increment usage counter
     */
    public function incrementUses(): void
    {
        $this->increment('current_uses');
    }

    /**
     * Check if investment amount qualifies
     */
    public function qualifiesForAmount(float $amount): bool
    {
        if (!$this->min_investment_amount) {
            return true;
        }

        return $amount >= $this->min_investment_amount;
    }
}
