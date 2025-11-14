<?php

namespace App\Modules\CarRentals\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalExtra extends Model
{
    use HasUuids;

    protected $table = 'rental_extras';

    protected $fillable = [
        'rental_id',
        'name',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'multilang_data',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'multilang_data' => 'array',
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    // Multilanguage support
    public function getTranslation(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->multilang_data[$locale][$field] ?? $this->$field ?? null;
    }
}
