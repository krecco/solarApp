<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * Sets the application locale based on:
     * 1. Query parameter (?lang=en)
     * 2. Accept-Language header
     * 3. Authenticated user's language preference
     * 4. System default language
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        // Validate the locale
        if (Language::isValidCode($locale)) {
            App::setLocale($locale);
        } else {
            // Fall back to default if invalid
            App::setLocale(Language::getDefaultCode());
        }

        return $next($request);
    }

    /**
     * Determine the locale to use for this request.
     */
    protected function determineLocale(Request $request): string
    {
        // 1. Check for explicit lang query parameter
        if ($request->has('lang')) {
            return $request->query('lang');
        }

        // 2. Check Accept-Language header
        $headerLang = $this->getLanguageFromHeader($request);
        if ($headerLang) {
            return $headerLang;
        }

        // 3. Check authenticated user's preferences
        if (Auth::check()) {
            $user = Auth::user();
            return $user->getLanguage();
        }

        // 4. Use system default
        return Language::getDefaultCode();
    }

    /**
     * Extract language code from Accept-Language header.
     */
    protected function getLanguageFromHeader(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header (e.g., "en-US,en;q=0.9,de;q=0.8")
        // Extract just the primary language codes
        $languages = [];
        foreach (explode(',', $acceptLanguage) as $lang) {
            $lang = trim(explode(';', $lang)[0]);
            $lang = strtolower(substr($lang, 0, 2)); // Get first 2 chars (ISO 639-1)
            if ($lang && !in_array($lang, $languages)) {
                $languages[] = $lang;
            }
        }

        // Return first valid language
        foreach ($languages as $lang) {
            if (Language::isValidCode($lang)) {
                return $lang;
            }
        }

        return null;
    }
}
