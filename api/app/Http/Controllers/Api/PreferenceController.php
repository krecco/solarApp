<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    /**
     * Get default preferences
     */
    private function getDefaults(): array
    {
        return [
            'language' => 'en',
            'theme' => 'light',
            'primaryColor' => '#3B82F6',
            'timezone' => 'UTC',
            'notifications' => [
                'email' => true,
                'push' => false,
                'system' => true,
                'subscription' => true,
                'tenant' => true,
            ],
        ];
    }

    /**
     * Get user preferences
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $preferences = $user->preferences ?? $this->getDefaults();

        // Ensure all keys exist
        $preferences = array_merge($this->getDefaults(), $preferences);

        return response()->json([
            'data' => $preferences,
        ]);
    }

    /**
     * Update user preferences
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'language' => 'sometimes|string|max:10',
            'theme' => 'sometimes|in:light,dark',
            'primaryColor' => 'sometimes|string|max:20',
            'timezone' => 'sometimes|string|max:50',
            'notifications' => 'sometimes|array',
            'notifications.email' => 'sometimes|boolean',
            'notifications.push' => 'sometimes|boolean',
            'notifications.system' => 'sometimes|boolean',
            'notifications.subscription' => 'sometimes|boolean',
            'notifications.tenant' => 'sometimes|boolean',
        ]);

        $user = $request->user();
        $currentPreferences = $user->preferences ?? $this->getDefaults();

        // Merge with current preferences
        if (isset($validated['notifications'])) {
            $validated['notifications'] = array_merge(
                $currentPreferences['notifications'] ?? $this->getDefaults()['notifications'],
                $validated['notifications']
            );
        }

        $newPreferences = array_merge($currentPreferences, $validated);

        $user->update(['preferences' => $newPreferences]);
        $user->refresh();

        return response()->json([
            'data' => $user->preferences,
            'meta' => [
                'status' => 'success',
                'message' => 'Preferences updated successfully',
            ],
        ]);
    }

    /**
     * Reset preferences to default
     */
    public function reset(Request $request): JsonResponse
    {
        $user = $request->user();
        $defaults = $this->getDefaults();

        $user->update(['preferences' => $defaults]);
        $user->refresh();

        return response()->json([
            'data' => $user->preferences,
            'meta' => [
                'status' => 'success',
                'message' => 'Preferences reset to defaults',
            ],
        ]);
    }
}
