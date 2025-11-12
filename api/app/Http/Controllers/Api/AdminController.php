<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role as SpatieRole;

class AdminController extends Controller
{
    /**
     * Get the full URL for an avatar
     */
    private function getAvatarUrl(?string $avatarPath): ?string
    {
        if (!$avatarPath) {
            return null;
        }

        // Get the URL from the public disk (configured with proper domain:port)
        return Storage::disk('public')->url($avatarPath);
    }

    /**
     * Get system dashboard statistics.
     */
    public function dashboard(Request $request): JsonResponse
    {
        // Build role counts dynamically from config
        $roleCounts = [];
        foreach (config('roles.available') as $role) {
            $roleCounts[$role . 's'] = User::role($role)->count();
        }

        $stats = [
            'users' => [
                'total' => User::count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
                'created_today' => User::whereDate('created_at', today())->count(),
                'created_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
                'created_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            ],
            'roles' => $roleCounts,
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * List all users with filtering and pagination.
     */
    public function users(Request $request): JsonResponse
    {
        // Validate request data
        $validated = $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
            'roles' => 'nullable|array',
            'roles.*' => ['string', Rule::in(config('roles.available'))],
            'email_verified' => 'nullable|boolean',
            'sort_by' => 'nullable|string|in:name,email,created_at,updated_at,email_verified_at,last_login_at',
            'sort_direction' => 'nullable|string|in:asc,desc',
        ]);

        $query = User::with('roles');

        // Apply role filter - support multiple roles
        if (!empty($validated['roles'])) {
            $query->whereHas('roles', function ($q) use ($validated) {
                $q->whereIn('name', $validated['roles']);
            });
        }

        // Apply email verification filter
        if (isset($validated['email_verified'])) {
            if ($validated['email_verified']) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        // Apply search filter
        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $validated['sort_by'] ?? 'last_login_at';
        $sortDirection = $validated['sort_direction'] ?? 'desc';

        // Handle NULL values properly for nullable columns
        if (in_array($sortBy, ['last_login_at', 'email_verified_at'])) {
            // For DESC: non-NULL values first, then NULLs
            // For ASC: NULLs first, then non-NULL values
            if ($sortDirection === 'desc') {
                $query->orderByRaw("$sortBy IS NULL ASC, $sortBy DESC");
            } else {
                $query->orderByRaw("$sortBy IS NULL DESC, $sortBy ASC");
            }
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Pagination
        $perPage = min($validated['per_page'] ?? 15, 100);
        $paginator = $query->paginate($perPage);

        // Transform the data to include role names
        $data = $paginator->getCollection()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $this->getAvatarUrl($user->avatar_url),
                'email_verified_at' => $user->email_verified_at,
                'last_login_at' => $user->last_login_at,
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return response()->json([
            'data' => $data->values(),
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Show a specific user.
     */
    public function showUser(User $user): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $this->getAvatarUrl($user->avatar_url),
                'email_verified_at' => $user->email_verified_at,
                'last_login_at' => $user->last_login_at,
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Create a new user.
     */
    public function createUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['nullable', Rule::in(config('roles.available'))],
            'email_verified' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => ($validated['email_verified'] ?? false) ? now() : null,
        ]);

        // Assign role (default to configured default role if not specified)
        $role = $validated['role'] ?? config('roles.default');
        $user->assignRole($role);

        return response()->json([
            'message' => 'User created successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $this->getAvatarUrl($user->avatar_url),
                'email_verified_at' => $user->email_verified_at,
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at,
            ]
        ], 201);
    }

    /**
     * Update an existing user.
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users')->ignore($user->id)
            ],
            'password' => 'nullable|string|min:8',
            'role' => ['nullable', Rule::in(config('roles.available'))],
            'email_verified' => 'nullable|boolean',
        ]);

        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }

        if (isset($validated['email'])) {
            $updateData['email'] = $validated['email'];
        }

        if (isset($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        if (isset($validated['email_verified'])) {
            $updateData['email_verified_at'] = $validated['email_verified'] ? now() : null;
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        // Update role if provided
        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $this->getAvatarUrl($user->avatar_url),
                'email_verified_at' => $user->email_verified_at,
                'roles' => $user->roles->pluck('name'),
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user): JsonResponse
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Send welcome email to a user.
     */
    public function sendWelcomeEmail(User $user): JsonResponse
    {
        try {
            // Send welcome email notification
            $user->notify(new \App\Notifications\WelcomeEmailNotification());

            return response()->json([
                'message' => 'Welcome email sent successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send welcome email: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to send welcome email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048', // Max 2MB
        ]);

        try {
            // Delete old avatar if exists
            if ($user->avatar_url) {
                Storage::disk('public')->delete($user->avatar_url);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');

            // Update user
            $user->update(['avatar_url' => $path]);

            return response()->json([
                'message' => 'Avatar updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $this->getAvatarUrl($user->avatar_url),
                    'email_verified_at' => $user->email_verified_at,
                    'last_login_at' => $user->last_login_at,
                    'roles' => $user->roles->pluck('name'),
                    'updated_at' => $user->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update avatar: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to update avatar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(User $user): JsonResponse
    {
        try {
            if ($user->avatar_url) {
                Storage::disk('public')->delete($user->avatar_url);
                $user->update(['avatar_url' => null]);

                return response()->json([
                    'message' => 'Avatar deleted successfully',
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar_url' => null,
                        'email_verified_at' => $user->email_verified_at,
                        'last_login_at' => $user->last_login_at,
                        'roles' => $user->roles->pluck('name'),
                        'updated_at' => $user->updated_at,
                    ]
                ]);
            }

            return response()->json([
                'message' => 'User has no avatar to delete'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to delete avatar: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to delete avatar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
