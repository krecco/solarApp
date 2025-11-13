<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Get all active languages
     */
    public function index(): JsonResponse
    {
        $languages = Language::active()->ordered()->get();

        return response()->json([
            'data' => $languages->map(function ($lang) {
                return [
                    'id' => $lang->id,
                    'code' => $lang->code,
                    'name' => $lang->name,
                    'native_name' => $lang->native_name,
                    'flag_emoji' => $lang->flag_emoji,
                    'is_default' => $lang->is_default,
                ];
            }),
        ]);
    }

    /**
     * Get user's current language preferences
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'data' => [
                'ui_language' => $user->getLanguage(),
                'document_language' => $user->getDocumentLanguage(),
                'email_language' => $user->getEmailLanguage(),
            ],
        ]);
    }

    /**
     * Update user's language preferences
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ui_language' => 'sometimes|string|size:2',
            'document_language' => 'sometimes|string|size:2',
            'email_language' => 'sometimes|string|size:2',
            'all_languages' => 'sometimes|string|size:2',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated',
            ], 401);
        }

        // Validate language codes
        foreach (['ui_language', 'document_language', 'email_language', 'all_languages'] as $key) {
            if (isset($validated[$key]) && !Language::isValidCode($validated[$key])) {
                return response()->json([
                    'error' => "Invalid language code for {$key}",
                    'message' => "Language code '{$validated[$key]}' is not supported",
                ], 422);
            }
        }

        // If all_languages is set, update all preferences to the same language
        if (isset($validated['all_languages'])) {
            $user->setAllLanguages($validated['all_languages']);
        } else {
            // Update individual language preferences
            if (isset($validated['ui_language'])) {
                $user->setLanguage($validated['ui_language']);
            }

            if (isset($validated['document_language'])) {
                $user->setDocumentLanguage($validated['document_language']);
            }

            if (isset($validated['email_language'])) {
                $user->setEmailLanguage($validated['email_language']);
            }
        }

        // Refresh user to get updated preferences
        $user->refresh();

        return response()->json([
            'data' => [
                'ui_language' => $user->getLanguage(),
                'document_language' => $user->getDocumentLanguage(),
                'email_language' => $user->getEmailLanguage(),
            ],
            'meta' => [
                'status' => 'success',
                'message' => 'Language preferences updated successfully',
            ],
        ]);
    }

    /**
     * Get the default language
     */
    public function getDefault(): JsonResponse
    {
        $default = Language::getDefault();

        if (!$default) {
            return response()->json([
                'error' => 'No default language found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'code' => $default->code,
                'name' => $default->name,
                'native_name' => $default->native_name,
                'flag_emoji' => $default->flag_emoji,
            ],
        ]);
    }
}
