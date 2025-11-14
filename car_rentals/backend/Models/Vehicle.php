<?php

namespace App\Modules\CarRentals\Models;

use App\Models\User;
use App\Modules\CarRentals\Enums\FuelType;
use App\Modules\CarRentals\Enums\TransmissionType;
use App\Modules\CarRentals\Enums\VehicleCategory;
use App\Modules\CarRentals\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'vehicles';

    protected $fillable = [
        'vin',
        'make',
        'model',
        'year',
        'color',
        'license_plate',
        'category',
        'transmission',
        'fuel_type',
        'seats',
        'doors',
        'mileage',
        'features',
        'daily_rate',
        'weekly_rate',
        'monthly_rate',
        'security_deposit',
        'status',
        'location',
        'owner_id',
        'manager_id',
        'file_container_id',
        'description',
        'multilang_data',
    ];

    protected $casts = [
        'features' => 'array',
        'multilang_data' => 'array',
        'mileage' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'weekly_rate' => 'decimal:2',
        'monthly_rate' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'category' => VehicleCategory::class,
        'transmission' => TransmissionType::class,
        'fuel_type' => FuelType::class,
        'status' => VehicleStatus::class,
        'year' => 'integer',
        'seats' => 'integer',
        'doors' => 'integer',
    ];

    // Relationships
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(VehicleReview::class);
    }

    public function maintenance(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    public function fileContainer(): MorphOne
    {
        return $this->morphOne(\App\Models\FileContainer::class, 'containerable');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', VehicleStatus::AVAILABLE);
    }

    public function scopeByCategory($query, VehicleCategory $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', $location);
    }

    // Multilanguage support
    public function getTranslation(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->multilang_data[$locale][$field] ?? $this->$field ?? null;
    }

    public function setTranslation(string $field, string $value, string $locale): void
    {
        $multilangData = $this->multilang_data ?? [];
        $multilangData[$locale][$field] = $value;
        $this->multilang_data = $multilangData;
    }

    // Helpers
    public function getFullName(): string
    {
        return "{$this->year} {$this->make} {$this->model}";
    }

    public function isAvailable(): bool
    {
        return $this->status === VehicleStatus::AVAILABLE;
    }

    public function getAverageRating(): float
    {
        return $this->reviews()->avg('rating') ?? 0.0;
    }

    public function getTotalReviews(): int
    {
        return $this->reviews()->count();
    }
}
