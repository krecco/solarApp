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
            'password' => 'hashed',
            'preferences' => 'array',
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
     * Get customer profile.
     */
    public function customerProfile()
    {
        return $this->hasOne(\App\Models\CustomerProfile::class);
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
        if (!$this->customerProfile) {
            return $this->name;
        }

        $parts = array_filter([
            $this->customerProfile->title_prefix,
            $this->name,
            $this->customerProfile->title_suffix,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Check if user is a customer type.
     */
    public function isCustomerType(string $type): bool
    {
        if (!$this->customerProfile) {
            return false;
        }

        return $this->customerProfile->customer_type === $type || $this->customerProfile->customer_type === 'both';
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
     * Backward compatibility accessors for customer profile fields
     * These delegate to the customerProfile relationship
     */

    public function getCustomerTypeAttribute()
    {
        return $this->customerProfile?->customer_type;
    }

    public function getCustomerNoAttribute()
    {
        return $this->customerProfile?->customer_no;
    }

    public function getIsBusinessAttribute()
    {
        return $this->customerProfile?->is_business ?? false;
    }

    public function getTitlePrefixAttribute()
    {
        return $this->customerProfile?->title_prefix;
    }

    public function getTitleSuffixAttribute()
    {
        return $this->customerProfile?->title_suffix;
    }

    public function getPhoneNrAttribute()
    {
        return $this->customerProfile?->phone_nr;
    }

    public function getGenderAttribute()
    {
        return $this->customerProfile?->gender;
    }

    public function getUserFilesVerifiedAttribute()
    {
        return $this->customerProfile?->user_files_verified ?? false;
    }

    public function getUserVerifiedAtAttribute()
    {
        return $this->customerProfile?->user_verified_at;
    }

    public function getDocumentExtraTextBlockAAttribute()
    {
        return $this->customerProfile?->document_extra_text_block_a;
    }

    public function getDocumentExtraTextBlockBAttribute()
    {
        return $this->customerProfile?->document_extra_text_block_b;
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

    /**
     * Messaging relationships
     */

    /**
     * Get conversations where user is a participant
     */
    public function conversations()
    {
        return $this->belongsToMany(\App\Models\Conversation::class, 'conversation_participants')
            ->withPivot(['last_read_at', 'unread_count'])
            ->withTimestamps();
    }

    /**
     * Get messages sent by user
     */
    public function sentMessages()
    {
        return $this->hasMany(\App\Models\Message::class, 'sender_id');
    }

    /**
     * Get total unread message count
     */
    public function getTotalUnreadMessagesCount(): int
    {
        return \DB::table('conversation_participants')
            ->where('user_id', $this->id)
            ->sum('unread_count');
    }
}
