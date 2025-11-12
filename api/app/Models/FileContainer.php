<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileContainer extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'type',
        'description',
    ];

    /**
     * Get all files in this container.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Get verified files only.
     */
    public function verifiedFiles(): HasMany
    {
        return $this->files()->where('is_verified', true);
    }

    /**
     * Get unverified files.
     */
    public function unverifiedFiles(): HasMany
    {
        return $this->files()->where('is_verified', false);
    }
}
