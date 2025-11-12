<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class OtpCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'code',
        'attempts',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Maximum number of verification attempts.
     */
    const MAX_ATTEMPTS = 5;

    /**
     * OTP validity duration in minutes.
     */
    const VALIDITY_MINUTES = 10;

    /**
     * OTP code length.
     */
    const CODE_LENGTH = 6;

    /**
     * Scope a query to only include valid OTP codes.
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now())
                     ->where('attempts', '<', self::MAX_ATTEMPTS);
    }

    /**
     * Check if the OTP code is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if max attempts have been reached.
     */
    public function hasReachedMaxAttempts(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Increment the attempts counter.
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /**
     * Generate a new OTP code.
     */
    public static function generateCode(): string
    {
        return str_pad(random_int(0, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new OTP for the given email.
     */
    public static function createForEmail(string $email): self
    {
        // Delete any existing OTP codes for this email
        static::where('email', $email)->delete();

        return static::create([
            'email' => $email,
            'code' => static::generateCode(),
            'expires_at' => Carbon::now()->addMinutes(static::VALIDITY_MINUTES),
        ]);
    }
}
