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

    /**
     * Language preference helper methods
     */

    /**
     * Get user's UI language preference.
     * Defaults to system default if not set.
     */
    public function getLanguage(): string
    {
        return $this->preferences['ui_language'] ?? Language::getDefaultCode();
    }

    /**
     * Get user's document language preference.
     * Falls back to UI language if not set separately.
     */
    public function getDocumentLanguage(): string
    {
        return $this->preferences['document_language']
            ?? $this->preferences['ui_language']
            ?? Language::getDefaultCode();
    }

    /**
     * Get user's email language preference.
     * Falls back to UI language if not set separately.
     */
    public function getEmailLanguage(): string
    {
        return $this->preferences['email_language']
            ?? $this->preferences['ui_language']
            ?? Language::getDefaultCode();
    }

    /**
     * Set user's UI language preference.
     */
    public function setLanguage(string $languageCode): void
    {
        $preferences = $this->preferences ?? [];
        $preferences['ui_language'] = $languageCode;
        $this->preferences = $preferences;
        $this->save();
    }

    /**
     * Set user's document language preference.
     */
    public function setDocumentLanguage(string $languageCode): void
    {
        $preferences = $this->preferences ?? [];
        $preferences['document_language'] = $languageCode;
        $this->preferences = $preferences;
        $this->save();
    }

    /**
     * Set user's email language preference.
     */
    public function setEmailLanguage(string $languageCode): void
    {
        $preferences = $this->preferences ?? [];
        $preferences['email_language'] = $languageCode;
        $this->preferences = $preferences;
        $this->save();
    }

    /**
     * Set all language preferences at once.
     */
    public function setAllLanguages(string $languageCode): void
    {
        $preferences = $this->preferences ?? [];
        $preferences['ui_language'] = $languageCode;
        $preferences['document_language'] = $languageCode;
        $preferences['email_language'] = $languageCode;
        $this->preferences = $preferences;
        $this->save();
    }

    /**
     * Get or create file container for this user.
     */
    public function fileContainer()
    {
        return $this->morphOne(\App\Models\FileContainer::class, 'containable');
    }

    /**
     * Get or create file container instance
     */
    public function getOrCreateFileContainer(): \App\Models\FileContainer
    {
        return $this->fileContainer()->firstOrCreate([
            'containable_type' => self::class,
            'containable_id' => $this->id,
        ], [
            'name' => "Documents for {$this->name}",
            'description' => 'User verification documents',
        ]);
    }

    /**
     * Document requirement helper methods
     */

    /**
     * Check if user has all required documents verified
     */
    public function hasAllRequiredDocuments(): bool
    {
        return app(\App\Services\DocumentRequirementService::class)
            ->hasAllRequiredDocuments($this);
    }

    /**
     * Get document verification completion percentage
     */
    public function getDocumentCompletionPercentage(): float
    {
        return app(\App\Services\DocumentRequirementService::class)
            ->getDocumentCompletionPercentage($this);
    }

    /**
     * Get document verification summary
     */
    public function getDocumentVerificationSummary(): array
    {
        return app(\App\Services\DocumentRequirementService::class)
            ->getDocumentVerificationSummary($this);
    }

    /**
     * Get missing required documents
     */
    public function getMissingRequiredDocuments()
    {
        return app(\App\Services\DocumentRequirementService::class)
            ->getMissingRequiredDocuments($this);
    }
}
