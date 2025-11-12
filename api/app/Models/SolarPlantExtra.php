<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolarPlantExtra extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'solar_plant_id',
        'extra_id',
        'price',
        'quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the solar plant.
     */
    public function solarPlant(): BelongsTo
    {
        return $this->belongsTo(SolarPlant::class);
    }

    /**
     * Get the extra/add-on.
     */
    public function extra(): BelongsTo
    {
        return $this->belongsTo(Extra::class);
    }

    /**
     * Get total cost.
     */
    public function getTotalCostAttribute(): float
    {
        return $this->price * $this->quantity;
    }
}
