<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Integer;

/**
 * Profile Completion Data - Step 2 of two-step flow
 * Used by ALL users (email and social) after initial registration.
 * Collects business information and creates tenant.
 */
class CompleteProfileData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $company_name,
        
        #[Required, StringType, Min(3), Max(63), Regex('/^[a-z0-9][a-z0-9-]*[a-z0-9]$/'), Unique('tenants', 'subdomain')]
        public string $subdomain,
        
        #[Nullable, Integer, Exists('plans', 'id')]
        public ?int $plan_id = null,
    ) {}
    
    /**
     * Custom messages for validation errors.
     */
    public static function messages(): array
    {
        return [
            'subdomain.regex' => 'The subdomain must contain only lowercase letters, numbers, and hyphens. It must start and end with a letter or number.',
            'subdomain.unique' => 'This subdomain is already taken. Please choose another one.',
        ];
    }
}
