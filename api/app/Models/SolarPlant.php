<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolarPlant extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'location',
        'address',
        'postal_code',
        'city',
        'country',
        'nominal_power',
        'annual_production',
        'consumption',
        'peak_power',
        'total_cost',
        'investment_needed',
        'kwh_price',
        'contract_duration_years',
        'interest_rate',
        'monthly_forecast',
        'repayment_calculation',
        'status',
        'start_date',
        'operational_date',
        'end_date',
        'user_id',
        'manager_id',
        'file_container_id',
        'contracts_signed',
        'contract_signed_at',
        'rs',
    ];

    protected $casts = [
        'nominal_power' => 'decimal:2',
        'annual_production' => 'decimal:2',
        'consumption' => 'decimal:2',
        'peak_power' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'investment_needed' => 'decimal:2',
        'kwh_price' => 'decimal:4',
        'interest_rate' => 'decimal:2',
        'monthly_forecast' => 'array',
        'repayment_calculation' => 'array',
        'start_date' => 'date',
        'operational_date' => 'date',
        'end_date' => 'date',
        'contracts_signed' => 'boolean',
        'contract_signed_at' => 'datetime',
    ];

    /**
     * Get the owner/customer of the solar plant.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the assigned manager.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the property owner details.
     */
    public function propertyOwner(): HasOne
    {
        return $this->hasOne(SolarPlantPropertyOwner::class);
    }

    /**
     * Get the extras/add-ons for this plant.
     */
    public function extras(): HasMany
    {
        return $this->hasMany(SolarPlantExtra::class);
    }

    /**
     * Get the investments for this plant.
     */
    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    /**
     * Get the repayment data.
     */
    public function repaymentData(): HasMany
    {
        return $this->hasMany(SolarPlantRepaymentData::class);
    }

    /**
     * Get the repayment logs.
     */
    public function repaymentLogs(): HasMany
    {
        return $this->hasMany(SolarPlantRepaymentLog::class);
    }

    /**
     * Get the file container.
     */
    public function fileContainer(): BelongsTo
    {
        return $this->belongsTo(FileContainer::class);
    }

    /**
     * Scope to filter by owner.
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by manager.
     */
    public function scopeManagedBy($query, $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get active plants.
     */
    public function scopeActive($query)
    {
        return $query->where('rs', 0);
    }

    /**
     * Calculate total investment received.
     */
    public function getTotalInvestedAttribute(): float
    {
        return $this->investments()
            ->where('status', 'active')
            ->sum('amount');
    }

    /**
     * Check if plant is fully funded.
     */
    public function getIsFullyFundedAttribute(): bool
    {
        if (!$this->investment_needed) {
            return false;
        }

        return $this->getTotalInvestedAttribute() >= $this->investment_needed;
    }
}
