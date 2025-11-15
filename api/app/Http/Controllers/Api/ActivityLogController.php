<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    protected ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }
    /**
     * Get activity logs
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject_type' => 'nullable|string',
            'subject_id' => 'nullable|string',
            'causer_id' => 'nullable|integer',
            'event' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Build query
        $query = Activity::query()->with(['subject', 'causer']);

        // Apply filters
        if ($request->has('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->has('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->has('event')) {
            $query->where('event', $request->event);
        }

        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhereJsonContains('properties', $search);
            });
        }

        // Role-based filtering
        $user = $request->user();
        if ($user->hasRole('customer', 'sanctum')) {
            // Customers can only see their own activities
            $query->where('causer_id', $user->id);
        } elseif ($user->hasRole('manager', 'sanctum')) {
            // Managers can see activities related to their managed plants and investments
            $query->where(function ($q) use ($user) {
                $q->where('causer_id', $user->id)
                  ->orWhereHasMorph('subject', ['App\Models\SolarPlant'], function ($query) use ($user) {
                      $query->where('manager_id', $user->id);
                  })
                  ->orWhereHasMorph('subject', ['App\Models\Investment'], function ($query) use ($user) {
                      $query->whereHas('solarPlant', function ($q) use ($user) {
                          $q->where('manager_id', $user->id);
                      });
                  });
            });
        }
        // Admin can see all activities

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        // Paginate
        $perPage = $request->input('per_page', 50);
        $logs = $query->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Get single activity log
     */
    public function show(Request $request, Activity $activity): JsonResponse
    {
        $user = $request->user();

        // Check access
        if (!$this->canViewActivity($user, $activity)) {
            return response()->json([
                'message' => 'Unauthorized to view this activity log',
            ], 403);
        }

        $activity->load(['subject', 'causer']);

        return response()->json([
            'data' => $activity,
        ]);
    }

    /**
     * Get activity statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = Activity::query();

        // Apply date filters
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        // Role-based filtering
        $user = $request->user();
        if ($user->hasRole('customer', 'sanctum')) {
            $query->where('causer_id', $user->id);
        } elseif ($user->hasRole('manager', 'sanctum')) {
            $query->where(function ($q) use ($user) {
                $q->where('causer_id', $user->id)
                  ->orWhereHasMorph('subject', ['App\Models\SolarPlant'], function ($query) use ($user) {
                      $query->where('manager_id', $user->id);
                  })
                  ->orWhereHasMorph('subject', ['App\Models\Investment'], function ($query) use ($user) {
                      $query->whereHas('solarPlant', function ($q) use ($user) {
                          $q->where('manager_id', $user->id);
                      });
                  });
            });
        }

        $statistics = [
            'total_activities' => (clone $query)->count(),
            'by_event' => (clone $query)
                ->selectRaw('event, count(*) as count')
                ->groupBy('event')
                ->orderBy('count', 'desc')
                ->get(),
            'by_subject_type' => (clone $query)
                ->selectRaw('subject_type, count(*) as count')
                ->groupBy('subject_type')
                ->orderBy('count', 'desc')
                ->get(),
            'recent_activity_count' => [
                'last_hour' => (clone $query)->where('created_at', '>=', now()->subHour())->count(),
                'last_24_hours' => (clone $query)->where('created_at', '>=', now()->subDay())->count(),
                'last_7_days' => (clone $query)->where('created_at', '>=', now()->subWeek())->count(),
                'last_30_days' => (clone $query)->where('created_at', '>=', now()->subMonth())->count(),
            ],
        ];

        return response()->json([
            'data' => $statistics,
        ]);
    }

    /**
     * Get activities for a specific model
     */
    public function forModel(Request $request, string $modelType, string $modelId): JsonResponse
    {
        $validator = Validator::make(['model_type' => $modelType], [
            'model_type' => 'required|in:investment,solar_plant,user,repayment,campaign,web_info,conversation,message',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid model type',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Map model type to class
        $modelClass = $this->getModelClass($modelType);

        // Check if model exists
        $model = $modelClass::find($modelId);
        if (!$model) {
            return response()->json([
                'message' => 'Model not found',
            ], 404);
        }

        // Check access
        $user = $request->user();
        if (!$this->canViewModelActivities($user, $model)) {
            return response()->json([
                'message' => 'Unauthorized to view activities for this model',
            ], 403);
        }

        // Get activities
        $activities = Activity::where('subject_type', $modelClass)
            ->where('subject_id', $modelId)
            ->with(['causer'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 50));

        return response()->json($activities);
    }

    /**
     * Get activities by a specific user
     */
    public function byUser(Request $request, int $userId): JsonResponse
    {
        $user = $request->user();

        // Check authorization
        if (!$user->hasRole('admin', 'sanctum') && $user->id !== $userId) {
            if ($user->hasRole('manager', 'sanctum')) {
                // Manager can view activities related to their managed entities
                // This is complex, so we'll allow it but filter in the query
            } else {
                return response()->json([
                    'message' => 'Unauthorized to view activities for this user',
                ], 403);
            }
        }

        $activities = Activity::where('causer_id', $userId)
            ->with(['subject', 'causer'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 50));

        return response()->json($activities);
    }

    /**
     * Check if user can view activity
     */
    protected function canViewActivity($user, Activity $activity): bool
    {
        if ($user->hasRole('admin', 'sanctum')) {
            return true;
        }

        if ($activity->causer_id === $user->id) {
            return true;
        }

        if ($user->hasRole('manager', 'sanctum') && $activity->subject) {
            if ($activity->subject_type === 'App\Models\SolarPlant') {
                return $activity->subject->manager_id === $user->id;
            }

            if ($activity->subject_type === 'App\Models\Investment') {
                return $activity->subject->solarPlant->manager_id === $user->id;
            }
        }

        return false;
    }

    /**
     * Check if user can view model activities
     */
    protected function canViewModelActivities($user, $model): bool
    {
        if ($user->hasRole('admin', 'sanctum')) {
            return true;
        }

        $modelType = get_class($model);

        switch ($modelType) {
            case 'App\Models\Investment':
                // Investor can view their own investments
                if ($user->id === $model->user_id) {
                    return true;
                }
                // Manager can view investments for their managed plants
                if ($user->hasRole('manager', 'sanctum') && $model->solarPlant->manager_id === $user->id) {
                    return true;
                }
                break;

            case 'App\Models\SolarPlant':
                // Owner can view their own plants
                if ($user->id === $model->user_id) {
                    return true;
                }
                // Manager can view managed plants
                if ($user->hasRole('manager', 'sanctum') && $model->manager_id === $user->id) {
                    return true;
                }
                break;

            case 'App\Models\User':
                // Users can only view their own activities
                return $user->id === $model->id;

            case 'App\Models\InvestmentRepayment':
                // Access through investment
                $investment = $model->investment;
                if ($user->id === $investment->user_id) {
                    return true;
                }
                if ($user->hasRole('manager', 'sanctum') && $investment->solarPlant->manager_id === $user->id) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Get timeline view of activities (grouped by date)
     */
    public function timeline(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|uuid',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = Activity::with(['subject', 'causer']);

        // Apply user filter
        if ($request->has('user_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('causer_id', $request->user_id)
                  ->orWhere('subject_id', $request->user_id);
            });
        }

        // Apply date filters
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        // Role-based filtering
        $user = $request->user();
        if ($user->hasRole('customer', 'sanctum')) {
            $query->where('causer_id', $user->id);
        }

        // Get activities
        $limit = $request->input('limit', 50);
        $activities = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // Group by date
        $timeline = $activities->groupBy(function ($activity) {
            return $activity->created_at->format('Y-m-d');
        })->map(function ($dayActivities, $date) {
            return [
                'date' => $date,
                'count' => $dayActivities->count(),
                'activities' => $dayActivities->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'description' => $activity->description,
                        'log_name' => $activity->log_name,
                        'subject_type' => class_basename($activity->subject_type),
                        'subject_id' => $activity->subject_id,
                        'causer' => $activity->causer ? [
                            'id' => $activity->causer->id,
                            'name' => $activity->causer->name ?? 'System',
                        ] : null,
                        'created_at' => $activity->created_at,
                    ];
                }),
            ];
        })->values();

        return response()->json([
            'data' => $timeline,
        ]);
    }

    /**
     * Export activity logs
     */
    public function export(Request $request): JsonResponse
    {
        // Only admin can export logs
        if (!$request->user()->hasRole('admin', 'sanctum')) {
            return response()->json([
                'message' => 'Unauthorized. Only administrators can export activity logs.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'sometimes|in:json,csv',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = Activity::with(['subject', 'causer'])
            ->where('created_at', '>=', $request->start_date)
            ->where('created_at', '<=', $request->end_date)
            ->orderBy('created_at', 'desc');

        $activities = $query->get();

        $exportData = $activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'description' => $activity->description,
                'log_name' => $activity->log_name,
                'event' => $activity->event,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'causer_type' => $activity->causer_type,
                'causer_id' => $activity->causer_id,
                'causer_name' => $activity->causer ? $activity->causer->name : 'System',
                'properties' => $activity->properties,
                'created_at' => $activity->created_at->toIso8601String(),
            ];
        });

        // Log the export
        $this->activityService->log('exported activity logs', null, $request->user(), [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'count' => $activities->count(),
        ]);

        return response()->json([
            'data' => [
                'export' => $exportData,
                'metadata' => [
                    'total_records' => $activities->count(),
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'exported_at' => now()->toIso8601String(),
                    'exported_by' => [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get recent activities (last 24 hours by default)
     */
    public function recent(Request $request): JsonResponse
    {
        $hours = $request->input('hours', 24);
        $hours = min($hours, 168); // Max 7 days

        $query = Activity::with(['subject', 'causer'])
            ->where('created_at', '>=', now()->subHours($hours));

        // Role-based filtering
        $user = $request->user();
        if ($user->hasRole('customer', 'sanctum')) {
            $query->where('causer_id', $user->id);
        } elseif ($user->hasRole('manager', 'sanctum')) {
            $query->where(function ($q) use ($user) {
                $q->where('causer_id', $user->id)
                  ->orWhereHasMorph('subject', ['App\Models\SolarPlant'], function ($query) use ($user) {
                      $query->where('manager_id', $user->id);
                  })
                  ->orWhereHasMorph('subject', ['App\Models\Investment'], function ($query) use ($user) {
                      $query->whereHas('solarPlant', function ($q) use ($user) {
                          $q->where('manager_id', $user->id);
                      });
                  });
            });
        }

        $activities = $query->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'log_name' => $activity->log_name,
                    'subject_type' => class_basename($activity->subject_type),
                    'subject_id' => $activity->subject_id,
                    'causer' => $activity->causer ? [
                        'id' => $activity->causer->id,
                        'name' => $activity->causer->name,
                    ] : null,
                    'created_at' => $activity->created_at,
                    'time_ago' => $activity->created_at->diffForHumans(),
                ];
            }),
            'meta' => [
                'hours' => $hours,
                'count' => $activities->count(),
            ],
        ]);
    }

    /**
     * Get model class from type
     */
    protected function getModelClass(string $modelType): string
    {
        $map = [
            'investment' => \App\Models\Investment::class,
            'solar_plant' => \App\Models\SolarPlant::class,
            'user' => \App\Models\User::class,
            'repayment' => \App\Models\InvestmentRepayment::class,
            'conversation' => \App\Models\Conversation::class,
            'message' => \App\Models\Message::class,
            'campaign' => \App\Models\Campaign::class,
            'web_info' => \App\Models\WebInfo::class,
        ];

        return $map[$modelType] ?? \App\Models\Investment::class;
    }
}
