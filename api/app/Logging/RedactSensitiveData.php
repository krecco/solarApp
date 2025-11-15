<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class RedactSensitiveData implements ProcessorInterface
{
    /**
     * List of sensitive keys to redact
     */
    private array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'token',
        'api_key',
        'api_secret',
        'secret',
        'credit_card',
        'card_number',
        'cvv',
        'ssn',
        'authorization',
        'bearer',
        'access_token',
        'refresh_token',
        'stripe_token',
        'stripe_secret',
        'sentry_dsn',
        'private_key',
        'aws_secret',
    ];

    /**
     * Process the log record
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $record['context'] = $this->redactArray($record['context']);
        $record['extra'] = $this->redactArray($record['extra']);

        return $record;
    }

    /**
     * Recursively redact sensitive data from arrays
     */
    private function redactArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->redactArray($value);
            } elseif ($this->isSensitiveKey($key)) {
                $data[$key] = '***REDACTED***';
            } elseif (is_string($value) && $this->containsSensitivePattern($value)) {
                $data[$key] = $this->redactSensitivePatterns($value);
            }
        }

        return $data;
    }

    /**
     * Check if a key is sensitive
     */
    private function isSensitiveKey(string $key): bool
    {
        $key = strtolower($key);

        foreach ($this->sensitiveKeys as $sensitiveKey) {
            if (str_contains($key, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a string contains sensitive patterns
     */
    private function containsSensitivePattern(string $value): bool
    {
        // Check for common sensitive patterns
        $patterns = [
            '/Bearer\s+[\w\-\.]+/i',
            '/Basic\s+[\w\-\.]+/i',
            '/key[_-]?[\w]{32,}/i',
            '/sk_live_[\w]+/i',  // Stripe live keys
            '/pk_live_[\w]+/i',  // Stripe public keys
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Redact sensitive patterns from strings
     */
    private function redactSensitivePatterns(string $value): string
    {
        $patterns = [
            '/Bearer\s+[\w\-\.]+/i' => 'Bearer ***REDACTED***',
            '/Basic\s+[\w\-\.]+/i' => 'Basic ***REDACTED***',
            '/key[_-]?[\w]{32,}/i' => 'key_***REDACTED***',
            '/sk_live_[\w]+/i' => 'sk_live_***REDACTED***',
            '/pk_live_[\w]+/i' => 'pk_live_***REDACTED***',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $value = preg_replace($pattern, $replacement, $value);
        }

        return $value;
    }
}
