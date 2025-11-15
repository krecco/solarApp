<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get user notifications with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unread_only' => 'sometimes|boolean',
            'category' => 'sometimes|in:system,subscription,tenant,feature',
            'type' => 'sometimes|in:info,warning,error,success',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
        ]);

        $query = $request->user()->notifications();

        // Apply filters
        if (!empty($validated['unread_only'])) {
            $query->where('is_read', false);
        }

        if (!empty($validated['category'])) {
            $query->where('category', $validated['category']);
        }

        if (!empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        // Order by created_at desc (newest first)
        $query->orderBy('created_at', 'desc');

        $perPage = $validated['per_page'] ?? 10;
        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
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
     * Get unread notifications count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->notifications()
            ->where('is_read', false)
            ->count();

        return response()->json([
            'data' => [
                'count' => $count,
            ],
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = $request->user()->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Notification marked as read',
            ],
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'All notifications marked as read',
            ],
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $notification = $request->user()->notifications()
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Notification deleted',
            ],
        ]);
    }

    /**
     * Clear all read notifications
     */
    public function clearRead(Request $request): JsonResponse
    {
        $request->user()->notifications()
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Read notifications cleared',
            ],
        ]);
    }

    /**
     * Send notification to a specific user (admin/manager only)
     */
    public function send(Request $request): JsonResponse
    {
        // Only admin and manager can send notifications
        if (!$request->user()->hasRole(['admin', 'manager'], 'sanctum')) {
            return response()->json([
                'message' => 'Unauthorized to send notifications',
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'nullable|in:info,warning,error,success',
            'category' => 'nullable|in:system,subscription,tenant,feature',
            'action_url' => 'nullable|url|max:500',
            'action_label' => 'nullable|string|max:100',
        ]);

        $notification = Notification::create([
            'user_id' => $validated['user_id'],
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'] ?? 'info',
            'category' => $validated['category'] ?? 'system',
            'action_url' => $validated['action_url'] ?? null,
            'action_label' => $validated['action_label'] ?? null,
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'Notification sent successfully',
            'data' => $notification,
        ], 201);
    }
}
