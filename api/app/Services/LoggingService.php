<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Throwable;

class LoggingService
{
    /**
     * Log an error with full context
     */
    public function error(string $message, ?Throwable $exception = null, array $context = []): void
    {
        $logContext = $this->buildContext($context);

        if ($exception) {
            $logContext = array_merge($logContext, [
                'exception_class' => get_class($exception),
                'exception_message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        Log::error($message, $logContext);

        // Also report to Sentry if configured
        if (app()->bound('sentry') && $exception) {
            app('sentry')->captureException($exception);
        }
    }

    /**
     * Log a warning
     */
    public function warning(string $message, ?Throwable $exception = null, array $context = []): void
    {
        $logContext = $this->buildContext($context);

        if ($exception) {
            $logContext['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        Log::warning($message, $logContext);
    }

    /**
     * Log info
     */
    public function info(string $message, array $context = []): void
    {
        Log::info($message, $this->buildContext($context, false));
    }

    /**
     * Log debug information
     */
    public function debug(string $message, array $context = []): void
    {
        Log::debug($message, $context);
    }

    /**
     * Build common context for all logs
     */
    private function buildContext(array $customContext = [], bool $includeRequest = true): array
    {
        $context = [
            'timestamp' => now()->toIso8601String(),
            'environment' => app()->environment(),
        ];

        // Add user context if authenticated
        if (Auth::check()) {
            $context['user_id'] = Auth::id();
            $context['user_email'] = Auth::user()->email;
        }

        // Add request context if needed
        if ($includeRequest && app()->runningInConsole() === false) {
            $context = array_merge($context, [
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        // Add memory usage in development
        if (app()->environment('local')) {
            $context['memory_usage'] = round(memory_get_usage() / 1024 / 1024, 2) . ' MB';
        }

        return array_merge($context, $customContext);
    }

    /**
     * Log database query errors
     */
    public function queryError(string $sql, array $bindings, Throwable $exception): void
    {
        $this->error('Database query failed', $exception, [
            'sql' => $sql,
            'bindings' => $bindings,
        ]);
    }

    /**
     * Log email sending errors
     */
    public function emailError(string $to, string $subject, Throwable $exception): void
    {
        $this->error('Email sending failed', $exception, [
            'to' => $to,
            'subject' => $subject,
        ]);
    }

    /**
     * Log API request errors
     */
    public function apiError(string $endpoint, string $method, Throwable $exception, array $data = []): void
    {
        $this->error('External API request failed', $exception, [
            'endpoint' => $endpoint,
            'method' => $method,
            'request_data' => $data,
        ]);
    }

    /**
     * Log file operation errors
     */
    public function fileError(string $operation, string $path, Throwable $exception): void
    {
        $this->error("File {$operation} failed", $exception, [
            'operation' => $operation,
            'path' => $path,
        ]);
    }

    /**
     * Log authentication errors
     */
    public function authError(string $email, string $reason): void
    {
        $this->warning('Authentication failed', null, [
            'email' => $email,
            'reason' => $reason,
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Log validation errors
     */
    public function validationError(string $context, array $errors): void
    {
        $this->warning('Validation failed', null, [
            'context' => $context,
            'errors' => $errors,
        ]);
    }
}
