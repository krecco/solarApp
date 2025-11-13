<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserProfileController extends Controller
{
    /**
     * Get user profile with all fields
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['addresses', 'sepaPermissions']);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'title_prefix' => $user->title_prefix,
                'title_suffix' => $user->title_suffix,
                'phone_nr' => $user->phone_nr,
                'gender' => $user->gender,
                'is_business' => $user->is_business,
                'customer_type' => $user->customer_type,
                'customer_no' => $user->customer_no,
                'status' => $user->status,
                'avatar_url' => $user->avatar_url,
                'preferences' => $user->preferences,
                'email_verified_at' => $user->email_verified_at,
                'user_verified_at' => $user->user_verified_at,
                'user_files_verified' => $user->user_files_verified,
                'roles' => $user->getRoleNames(),
                'addresses' => $user->addresses,
                'sepa_permissions' => $user->sepaPermissions,
            ],
        ]);
    }

    /**
     * Update user profile.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'title_prefix' => 'nullable|string|max:50',
            'title_suffix' => 'nullable|string|max:50',
            'phone_nr' => 'nullable|string|max:50',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'is_business' => 'sometimes|boolean',
            'customer_type' => 'nullable|in:investor,plant_owner,both',
            'document_extra_text_block_a' => 'nullable|string|max:1000',
            'document_extra_text_block_b' => 'nullable|string|max:1000',
        ]);

        $user->update($validated);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'title_prefix' => $user->title_prefix,
                'title_suffix' => $user->title_suffix,
                'phone_nr' => $user->phone_nr,
                'gender' => $user->gender,
                'is_business' => $user->is_business,
                'customer_type' => $user->customer_type,
                'avatar_url' => $user->avatar_url,
            ],
            'meta' => [
                'status' => 'success',
                'message' => 'Profile updated successfully',
            ],
        ]);
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Password updated successfully',
            ],
        ]);
    }
}
