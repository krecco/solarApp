<?php

namespace App\Modules\CarRentals\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMaintenance extends Model
{
    use HasUuids;

    protected $table = 'vehicle_maintenance';

    public $timestamps = false;

    protected $fillable = [
        'vehicle_id',
        'maintenance_type',
        'description',
        'cost',
        'performed_by',
        'performed_at',
        'next_maintenance_date',
        'created_at',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'performed_at' => 'datetime',
        'next_maintenance_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('maintenance_type', $type);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereNotNull('next_maintenance_date')
                     ->where('next_maintenance_date', '>=', now());
    }
}
