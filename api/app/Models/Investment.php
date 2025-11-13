<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'solar_plant_id',
        'amount',
        'duration_months',
        'interest_rate',
        'repayment_interval',
        'status',
        'contract_status',
        'verified',
        'verified_at',
        'verified_by',
        'file_container_id',
        'start_date',
        'end_date',
        'total_repayment',
        'total_interest',
        'paid_amount',
        'notes',
        'document_language',
        'rs',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_repayment' => 'decimal:2',
        'total_interest' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the investor.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the solar plant.
     */
    public function solarPlant(): BelongsTo
    {
        return $this->belongsTo(SolarPlant::class);
    }

    /**
     * Get the user who verified this investment.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the file container.
     */
    public function fileContainer(): BelongsTo
    {
        return $this->belongsTo(FileContainer::class);
    }

    /**
     * Get the repayments.
     */
    public function repayments(): HasMany
    {
        return $this->hasMany(InvestmentRepayment::class);
    }

    /**
     * Scope to filter by investor.
     */
    public function scopeByInvestor($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get verified investments.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Scope to get active investments.
     */
    public function scopeActive($query)
    {
        return $query->where('rs', 0)
            ->where('status', 'active');
    }

    /**
     * Calculate remaining balance.
     */
    public function getRemainingBalanceAttribute(): float
    {
        if (!$this->total_repayment) {
            return 0;
        }

        return $this->total_repayment - $this->paid_amount;
    }

    /**
     * Calculate completion percentage.
     */
    public function getCompletionPercentageAttribute(): float
    {
        if (!$this->total_repayment || $this->total_repayment == 0) {
            return 0;
        }

        return ($this->paid_amount / $this->total_repayment) * 100;
    }

    /**
     * Get next repayment due.
     */
    public function getNextRepaymentAttribute()
    {
        return $this->repayments()
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->first();
    }
}
