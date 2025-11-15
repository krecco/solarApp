<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Messaging Controller
 *
 * Handles messaging/chat functionality between admins/managers and customers
 */
class MessagingController extends Controller
{
    protected ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }
    /**
     * Get all conversations for authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Conversation::forUser($user->id)
            ->with(['participants', 'latestMessage.sender', 'creator'])
            ->latestActivity();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->active(); // Default to active conversations
        }

        // Search by subject
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('subject', 'ILIKE', "%{$search}%");
        }

        $perPage = $request->input('per_page', 20);
        $conversations = $query->paginate($perPage);

        return response()->json([
            'data' => $conversations->map(function ($conversation) use ($user) {
                return $this->formatConversation($conversation, $user->id);
            }),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    /**
     * Create a new conversation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'required|uuid|exists:users,id',
            'subject' => 'nullable|string|max:255',
            'initial_message' => 'required|string|max:5000',
        ]);

        // Check if conversation already exists with same participants
        $participantIds = array_merge([$user->id], $validated['participant_ids']);
        sort($participantIds);

        $existingConversation = Conversation::query()
            ->whereHas('participants', function ($query) use ($participantIds) {
                $query->whereIn('users.id', $participantIds)
                    ->groupBy('conversation_id')
                    ->havingRaw('COUNT(DISTINCT users.id) = ?', [count($participantIds)]);
            })
            ->first();

        if ($existingConversation) {
            // Use existing conversation
            $conversation = $existingConversation;
        } else {
            // Create new conversation
            $conversation = Conversation::create([
                'subject' => $validated['subject'] ?? 'New Conversation',
                'status' => 'active',
                'created_by_id' => $user->id,
                'last_message_at' => now(),
            ]);

            // Add all participants
            foreach ($participantIds as $participantId) {
                $conversation->addParticipant($participantId);
            }
        }

        // Send initial message
        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $validated['initial_message'],
            'type' => 'text',
        ]);

        // Log activity
        $this->activityService->log('created conversation', $conversation, $user, [
            'participant_ids' => $participantIds,
        ]);

        return response()->json([
            'data' => $this->formatConversation($conversation->load(['participants', 'latestMessage.sender']), $user->id),
            'meta' => [
                'status' => 'success',
                'message' => 'Conversation created successfully.',
            ],
        ], 201);
    }

    /**
     * Get a specific conversation with messages
     *
     * @param Request $request
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You are not a participant in this conversation.',
            ], 403);
        }

        $conversation->load(['participants', 'latestMessage.sender', 'creator']);

        // Get messages with pagination
        $perPage = $request->input('per_page', 50);
        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Mark conversation as read for this user
        $conversation->markAsReadForUser($user->id);

        return response()->json([
            'data' => [
                'conversation' => $this->formatConversation($conversation, $user->id),
                'messages' => $messages->map(fn($message) => $this->formatMessage($message, $user->id)),
            ],
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    /**
     * Send a message in a conversation
     *
     * @param Request $request
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You are not a participant in this conversation.',
            ], 403);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
            'type' => 'sometimes|in:text,system',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $validated['body'],
            'type' => $validated['type'] ?? 'text',
        ]);

        // Log activity
        $this->activityService->log('sent message', $message, $user);

        // TODO: Send notification to other participants

        return response()->json([
            'data' => $this->formatMessage($message->load('sender'), $user->id),
            'meta' => [
                'status' => 'success',
                'message' => 'Message sent successfully.',
            ],
        ], 201);
    }

    /**
     * Mark conversation as read
     *
     * @param Request $request
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You are not a participant in this conversation.',
            ], 403);
        }

        $conversation->markAsReadForUser($user->id);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Conversation marked as read.',
            ],
        ]);
    }

    /**
     * Archive a conversation
     *
     * @param Request $request
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function archive(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You are not a participant in this conversation.',
            ], 403);
        }

        $conversation->archive();

        $this->activityService->log('archived conversation', $conversation, $user);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Conversation archived successfully.',
            ],
        ]);
    }

    /**
     * Reactivate a conversation
     *
     * @param Request $request
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function reactivate(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user->id)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You are not a participant in this conversation.',
            ], 403);
        }

        $conversation->reactivate();

        $this->activityService->log('reactivated conversation', $conversation, $user);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Conversation reactivated successfully.',
            ],
        ]);
    }

    /**
     * Get unread message count for authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalUnread = DB::table('conversation_participants')
            ->where('user_id', $user->id)
            ->sum('unread_count');

        $conversationsWithUnread = DB::table('conversation_participants')
            ->where('user_id', $user->id)
            ->where('unread_count', '>', 0)
            ->count();

        return response()->json([
            'data' => [
                'total_unread_messages' => $totalUnread,
                'conversations_with_unread' => $conversationsWithUnread,
            ],
        ]);
    }

    /**
     * Search for users to start a conversation with
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2',
            'role' => 'sometimes|in:admin,manager,customer',
        ]);

        $query = User::query()
            ->where(function ($q) use ($validated) {
                $q->where('name', 'ILIKE', "%{$validated['query']}%")
                    ->orWhere('email', 'ILIKE', "%{$validated['query']}%")
                    ->orWhere('customer_no', 'ILIKE', "%{$validated['query']}%");
            });

        // Filter by role if specified
        if (isset($validated['role'])) {
            $query->role($validated['role']);
        }

        $users = $query->limit(10)->get();

        return response()->json([
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'customer_no' => $user->customer_no,
                    'avatar_url' => $user->avatar_url,
                    'roles' => $user->getRoleNames(),
                ];
            }),
        ]);
    }

    /**
     * Format conversation for API response
     *
     * @param Conversation $conversation
     * @param string $currentUserId
     * @return array
     */
    protected function formatConversation(Conversation $conversation, string $currentUserId): array
    {
        $otherParticipants = $conversation->getOtherParticipants($currentUserId);
        $latestMessage = $conversation->latestMessage;

        return [
            'id' => $conversation->id,
            'subject' => $conversation->subject,
            'status' => $conversation->status,
            'created_by' => $conversation->creator ? [
                'id' => $conversation->creator->id,
                'name' => $conversation->creator->name,
            ] : null,
            'participants' => $conversation->participants->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'email' => $p->email,
                'avatar_url' => $p->avatar_url,
                'last_read_at' => $p->pivot->last_read_at,
            ]),
            'other_participants' => $otherParticipants->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'avatar_url' => $p->avatar_url,
            ])->values(),
            'unread_count' => $conversation->getUnreadCountForUser($currentUserId),
            'last_message' => $latestMessage ? [
                'id' => $latestMessage->id,
                'body' => $latestMessage->body,
                'sender' => [
                    'id' => $latestMessage->sender->id,
                    'name' => $latestMessage->sender->name,
                ],
                'created_at' => $latestMessage->created_at,
            ] : null,
            'last_message_at' => $conversation->last_message_at,
            'created_at' => $conversation->created_at,
            'updated_at' => $conversation->updated_at,
        ];
    }

    /**
     * Format message for API response
     *
     * @param Message $message
     * @param string $currentUserId
     * @return array
     */
    protected function formatMessage(Message $message, string $currentUserId): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender' => [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'avatar_url' => $message->sender->avatar_url,
            ],
            'body' => $message->body,
            'type' => $message->type,
            'attachments' => $message->attachments,
            'is_read' => $message->is_read,
            'read_at' => $message->read_at,
            'is_own_message' => $message->sender_id === $currentUserId,
            'created_at' => $message->created_at,
            'updated_at' => $message->updated_at,
        ];
    }
}
