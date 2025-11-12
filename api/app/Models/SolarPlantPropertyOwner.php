<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolarPlantPropertyOwner extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'solar_plant_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'notes',
    ];

    /**
     * Get the solar plant.
     */
    public function solarPlant(): BelongsTo
    {
        return $this->belongsTo(SolarPlant::class);
    }

    /**
     * Get full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
