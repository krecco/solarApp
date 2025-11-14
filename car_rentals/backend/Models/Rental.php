<?php

namespace App\Modules\CarRentals\Models;

use App\Models\User;
use App\Modules\CarRentals\Enums\RentalStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rental extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'rentals';

    protected $fillable = [
        'rental_number',
        'user_id',
        'vehicle_id',
        'pickup_date',
        'return_date',
        'actual_pickup_date',
        'actual_return_date',
        'daily_rate',
        'total_days',
        'subtotal',
        'tax_amount',
        'insurance_fee',
        'extras_total',
        'total_amount',
        'security_deposit',
        'payment_status',
        'payment_method',
        'payment_date',
        'status',
        'verification_status',
        'verified_by',
        'verified_at',
        'pickup_mileage',
        'return_mileage',
        'mileage_limit',
        'excess_mileage',
        'pickup_condition',
        'return_condition',
        'damage_report',
        'damage_cost',
        'file_container_id',
        'notes',
        'document_language',
    ];

    protected $casts = [
        'pickup_date' => 'datetime',
        'return_date' => 'datetime',
        'actual_pickup_date' => 'datetime',
        'actual_return_date' => 'datetime',
        'payment_date' => 'datetime',
        'verified_at' => 'datetime',
        'daily_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'extras_total' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'pickup_mileage' => 'decimal:2',
        'return_mileage' => 'decimal:2',
        'mileage_limit' => 'decimal:2',
        'excess_mileage' => 'decimal:2',
        'damage_cost' => 'decimal:2',
        'total_days' => 'integer',
        'status' => RentalStatus::class,
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function extras(): HasMany
    {
        return $this->hasMany(RentalExtra::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(RentalPayment::class);
    }

    public function fileContainer(): MorphOne
    {
        return $this->morphOne(\App\Models\FileContainer::class, 'containerable');
    }

    // Scopes
    public function scopeByStatus($query, RentalStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', RentalStatus::ACTIVE);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', RentalStatus::ACTIVE)
                     ->where('return_date', '<', now());
    }

    // Business logic
    public function calculateTotalDays(): int
    {
        return $this->pickup_date->diffInDays($this->return_date);
    }

    public function calculateSubtotal(): float
    {
        $days = $this->calculateTotalDays();
        return $days * $this->daily_rate;
    }

    public function calculateTax(float $taxRate = 0.19): float
    {
        return $this->subtotal * $taxRate;
    }

    public function calculateTotal(): float
    {
        return $this->subtotal + $this->tax_amount + $this->insurance_fee + $this->extras_total;
    }

    public function calculateExcessMileage(): float
    {
        if (!$this->return_mileage || !$this->pickup_mileage || !$this->mileage_limit) {
            return 0;
        }

        $totalMileage = $this->return_mileage - $this->pickup_mileage;
        $allowedMileage = $this->mileage_limit * $this->total_days;
        $excess = $totalMileage - $allowedMileage;

        return max(0, $excess);
    }

    public function isOverdue(): bool
    {
        return $this->status === RentalStatus::ACTIVE
            && now()->isAfter($this->return_date);
    }

    public function canTransitionTo(RentalStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    // Generate rental number
    public static function generateRentalNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now())->count() + 1;
        return "RNT-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
