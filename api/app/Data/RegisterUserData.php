<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\Validation\Nullable;

/**
 * Simplified Registration Data - Step 1 of two-step flow
 * Used for BOTH email and social registration.
 * Only collects basic user information.
 */
class RegisterUserData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $name,
        
        #[Required, Email, Unique('users', 'email')]
        public string $email,
        
        // Password is nullable for social login users
        #[Nullable, StringType, Min(8), Confirmed]
        public ?string $password = null,
        
        #[Nullable]
        public ?string $password_confirmation = null,
    ) {}
}
