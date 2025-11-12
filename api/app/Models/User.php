<?php

namespace App\Models;

use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'email_verified_at',
        'last_login_at',
        'preferences',
        // Solar app fields
        'title_prefix',
        'title_suffix',
        'phone_nr',
        'gender',
        'is_business',
        'customer_type',
        'user_files_verified',
        'user_verified_at',
        'document_extra_text_block_a',
        'document_extra_text_block_b',
        'customer_no',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'user_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'is_business' => 'boolean',
            'user_files_verified' => 'boolean',
        ];
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * Get the user's notifications.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Solar plant relationships
     */

    /**
     * Get solar plants owned by this user.
     */
    public function solarPlants()
    {
        return $this->hasMany(\App\Models\SolarPlant::class);
    }

    /**
     * Get solar plants managed by this user (if manager/admin).
     */
    public function managedSolarPlants()
    {
        return $this->hasMany(\App\Models\SolarPlant::class, 'manager_id');
    }

    /**
     * Get investments made by this user.
     */
    public function investments()
    {
        return $this->hasMany(\App\Models\Investment::class);
    }

    /**
     * Get user addresses.
     */
    public function addresses()
    {
        return $this->hasMany(\App\Models\UserAddress::class);
    }

    /**
     * Get SEPA permissions.
     */
    public function sepaPermissions()
    {
        return $this->hasMany(\App\Models\UserSepaPermission::class);
    }

    /**
     * Get full name with prefix and suffix.
     */
    public function getFullNameWithTitlesAttribute(): string
    {
        $parts = array_filter([
            $this->title_prefix,
            $this->name,
            $this->title_suffix,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Check if user is a customer type.
     */
    public function isCustomerType(string $type): bool
    {
        return $this->customer_type === $type || $this->customer_type === 'both';
    }

    /**
     * Check if user is an investor.
     */
    public function isInvestor(): bool
    {
        return $this->isCustomerType('investor');
    }

    /**
     * Check if user is a plant owner.
     */
    public function isPlantOwner(): bool
    {
        return $this->isCustomerType('plant_owner');
    }
}
