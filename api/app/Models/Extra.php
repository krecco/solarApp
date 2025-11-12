<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Extra extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'default_price',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all plant extras using this extra.
     */
    public function solarPlantExtras(): HasMany
    {
        return $this->hasMany(SolarPlantExtra::class);
    }

    /**
     * Scope to get active extras.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
