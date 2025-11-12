<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    /**
     * Encrypt a value.
     */
    public function encrypt(string $value): string
    {
        return Crypt::encryptString($value);
    }

    /**
     * Decrypt a value.
     */
    public function decrypt(string $encrypted): string
    {
        return Crypt::decryptString($encrypted);
    }

    /**
     * Encrypt an array or object.
     */
    public function encryptArray(array $data): string
    {
        return $this->encrypt(json_encode($data));
    }

    /**
     * Decrypt to an array.
     */
    public function decryptArray(string $encrypted): array
    {
        return json_decode($this->decrypt($encrypted), true);
    }

    /**
     * Generate a secure random password.
     */
    public function generatePassword(int $length = 32): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        $password = '';
        $max = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $max)];
        }

        return $password;
    }

    /**
     * Hash a value using SHA256.
     */
    public function hash(string $value): string
    {
        return hash('sha256', $value);
    }

    /**
     * Generate a secure token.
     */
    public function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Create HMAC signature for webhook verification.
     */
    public function createHmacSignature(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Verify HMAC signature.
     */
    public function verifyHmacSignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = $this->createHmacSignature($payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Safely compare two strings (timing attack safe).
     */
    public function safeCompare(string $known, string $user): bool
    {
        return hash_equals($known, $user);
    }
}
