<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolarPlant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SolarPlantController extends Controller
{
    /**
     * Display a listing of solar plants.
     */
    public function index(Request $request): JsonResponse
    {
        // TEMPORARY: Remove eager loading to debug
        $query = SolarPlant::query()->where('rs', 0);

        // Role-based filtering
        $user = $request->user();

        // DEBUG LOGGING
        \Log::info('SolarPlant::index called', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'roles_sanctum' => $user->roles()->where('guard_name', 'sanctum')->pluck('name')->toArray(),
            'hasRole_admin' => $user->hasRole('admin', 'sanctum'),
            'hasRole_manager' => $user->hasRole('manager', 'sanctum'),
            'hasRole_customer' => $user->hasRole('customer', 'sanctum'),
            'total_plants_before_filter' => SolarPlant::where('rs', 0)->count(),
        ]);

        if ($user->hasRole('customer', 'sanctum')) {
            // Customers only see their own plants
            \Log::info('Applying CUSTOMER filter', ['user_id' => $user->id]);
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('manager', 'sanctum')) {
            // Managers see plants assigned to them
            \Log::info('Applying MANAGER filter', ['user_id' => $user->id]);
            $query->where('manager_id', $user->id);
        } else {
            \Log::info('NO FILTER applied - showing all plants (admin)');
        }
        // Admins see all plants

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // DEBUG: Log the SQL query
        \Log::info('SQL Query', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $plants = $query->paginate($perPage);

        \Log::info('SolarPlant::index result', ['count' => $plants->total(), 'items' => $plants->count()]);

        return response()->json($plants);
    }

    /**
     * Store a newly created solar plant.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:2',
            'nominal_power' => 'required|numeric|min:0',
            'annual_production' => 'nullable|numeric|min:0',
            'consumption' => 'nullable|numeric|min:0',
            'peak_power' => 'nullable|numeric|min:0',
            'total_cost' => 'required|numeric|min:0',
            'investment_needed' => 'nullable|numeric|min:0',
            'kwh_price' => 'nullable|numeric|min:0',
            'contract_duration_years' => 'nullable|integer|min:1|max:50',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|string|in:draft,active,funded,operational,completed,cancelled',
            'start_date' => 'nullable|date',
            'operational_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'user_id' => 'required|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $plant = SolarPlant::create($validator->validated());

        // Log activity
        activity()
            ->performedOn($plant)
            ->causedBy($request->user())
            ->log('created solar plant');

        return response()->json([
            'message' => 'Solar plant created successfully',
            'data' => $plant->load(['owner', 'manager']),
        ], 201);
    }

    /**
     * Display the specified solar plant.
     */
    public function show(Request $request, SolarPlant $solarPlant): JsonResponse
    {
        // Authorization check
        $user = $request->user();
        if ($user->hasRole('customer', 'sanctum') && $solarPlant->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($user->hasRole('manager', 'sanctum') && $solarPlant->manager_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $plant = $solarPlant->load([
            'owner',
            'manager',
            'propertyOwner',
            'extras.extra',
            'investments.user',
            'repaymentData',
            'fileContainer.files',
        ]);

        return response()->json(['data' => $plant]);
    }

    /**
     * Update the specified solar plant.
     */
    public function update(Request $request, SolarPlant $solarPlant): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:2',
            'nominal_power' => 'sometimes|required|numeric|min:0',
            'annual_production' => 'nullable|numeric|min:0',
            'consumption' => 'nullable|numeric|min:0',
            'peak_power' => 'nullable|numeric|min:0',
            'total_cost' => 'sometimes|required|numeric|min:0',
            'investment_needed' => 'nullable|numeric|min:0',
            'kwh_price' => 'nullable|numeric|min:0',
            'contract_duration_years' => 'nullable|integer|min:1|max:50',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|string|in:draft,active,funded,operational,completed,cancelled',
            'start_date' => 'nullable|date',
            'operational_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldValues = $solarPlant->toArray();
        $solarPlant->update($validator->validated());

        // Log activity
        activity()
            ->performedOn($solarPlant)
            ->causedBy($request->user())
            ->withProperties([
                'old' => $oldValues,
                'new' => $solarPlant->toArray(),
            ])
            ->log('updated solar plant');

        return response()->json([
            'message' => 'Solar plant updated successfully',
            'data' => $solarPlant->load(['owner', 'manager']),
        ]);
    }

    /**
     * Remove the specified solar plant.
     */
    public function destroy(Request $request, SolarPlant $solarPlant): JsonResponse
    {
        // Check if there are active investments
        if ($solarPlant->investments()->where('status', 'active')->exists()) {
            return response()->json([
                'message' => 'Cannot delete solar plant with active investments',
            ], 422);
        }

        // Soft delete
        $solarPlant->rs = 99;
        $solarPlant->save();
        $solarPlant->delete();

        // Log activity
        activity()
            ->performedOn($solarPlant)
            ->causedBy($request->user())
            ->log('deleted solar plant');

        return response()->json([
            'message' => 'Solar plant deleted successfully',
        ]);
    }

    /**
     * Update solar plant status.
     */
    public function updateStatus(Request $request, SolarPlant $solarPlant): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:draft,active,funded,operational,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldStatus = $solarPlant->status;
        $solarPlant->update(['status' => $request->status]);

        // Log activity
        activity()
            ->performedOn($solarPlant)
            ->causedBy($request->user())
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => $request->status,
            ])
            ->log('changed solar plant status');

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => $solarPlant,
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = SolarPlant::where('rs', 0);

        // Apply role-based filtering
        if ($user->hasRole('customer', 'sanctum')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('manager', 'sanctum')) {
            $query->where('manager_id', $user->id);
        }

        $statistics = [
            'total' => $query->count(),
            'by_status' => [
                'draft' => (clone $query)->where('status', 'draft')->count(),
                'active' => (clone $query)->where('status', 'active')->count(),
                'funded' => (clone $query)->where('status', 'funded')->count(),
                'operational' => (clone $query)->where('status', 'operational')->count(),
                'completed' => (clone $query)->where('status', 'completed')->count(),
            ],
            'total_power' => (clone $query)->sum('nominal_power'),
            'total_cost' => (clone $query)->sum('total_cost'),
        ];

        return response()->json($statistics);
    }
}
