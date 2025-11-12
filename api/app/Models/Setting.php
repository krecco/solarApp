<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivity;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
        'is_public',
        'rs',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['group', 'key', 'value', 'type', 'is_public'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the setting value with proper type casting
     */
    public function getTypedValue()
    {
        switch ($this->type) {
            case 'integer':
                return (int) $this->value;
            case 'boolean':
                return in_array($this->value, [1, '1', 'true', true], true);
            case 'decimal':
                return (float) $this->value;
            case 'json':
                return is_string($this->value) ? json_decode($this->value, true) : $this->value;
            case 'string':
            default:
                return $this->value;
        }
    }

    /**
     * Scope to get settings by group
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to get public settings
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to exclude soft deleted
     */
    public function scopeActive($query)
    {
        return $query->where('rs', 0);
    }
}
