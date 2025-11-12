<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolarPlantRepaymentLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'solar_plant_id',
        'solar_plant_repayment_data_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the solar plant.
     */
    public function solarPlant(): BelongsTo
    {
        return $this->belongsTo(SolarPlant::class);
    }

    /**
     * Get the repayment data.
     */
    public function repaymentData(): BelongsTo
    {
        return $this->belongsTo(SolarPlantRepaymentData::class, 'solar_plant_repayment_data_id');
    }
}
