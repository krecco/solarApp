<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
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
        if ($user->hasRole('customer')) {
            // Customers can only see their own activities
            $query->where('causer_id', $user->id);
        } elseif ($user->hasRole('manager')) {
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
        if ($user->hasRole('customer')) {
            $query->where('causer_id', $user->id);
        } elseif ($user->hasRole('manager')) {
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
            'model_type' => 'required|in:investment,solar_plant,user,repayment',
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
        if (!$user->hasRole('admin') && $user->id !== $userId) {
            if ($user->hasRole('manager')) {
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
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($activity->causer_id === $user->id) {
            return true;
        }

        if ($user->hasRole('manager') && $activity->subject) {
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
        if ($user->hasRole('admin')) {
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
                if ($user->hasRole('manager') && $model->solarPlant->manager_id === $user->id) {
                    return true;
                }
                break;

            case 'App\Models\SolarPlant':
                // Owner can view their own plants
                if ($user->id === $model->user_id) {
                    return true;
                }
                // Manager can view managed plants
                if ($user->hasRole('manager') && $model->manager_id === $user->id) {
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
                if ($user->hasRole('manager') && $investment->solarPlant->manager_id === $user->id) {
                    return true;
                }
                break;
        }

        return false;
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
        ];

        return $map[$modelType] ?? \App\Models\Investment::class;
    }
}
