<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag_emoji',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope to get only active languages.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order languages by sort order.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope to get the default language.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the default language.
     */
    public static function getDefault(): ?Language
    {
        return static::default()->first();
    }

    /**
     * Get all active languages ordered by sort order.
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->ordered()->get();
    }

    /**
     * Check if a language code is valid and active.
     */
    public static function isValidCode(string $code): bool
    {
        return static::active()->where('code', $code)->exists();
    }

    /**
     * Get a language by code.
     */
    public static function getByCode(string $code): ?Language
    {
        return static::where('code', $code)->first();
    }

    /**
     * Get the default language code.
     */
    public static function getDefaultCode(): string
    {
        $default = static::getDefault();
        return $default ? $default->code : 'en';
    }
}
