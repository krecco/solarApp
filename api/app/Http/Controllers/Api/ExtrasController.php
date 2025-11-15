<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Extra;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Extras/Add-ons Controller
 *
 * Manages additional services and products that can be added to solar plants or investments.
 * Examples: battery storage, monitoring systems, maintenance packages, etc.
 */
class ExtrasController extends Controller
{
    protected ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }
    /**
     * List all extras with optional filtering
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Extra::query();

        // Filter by active status
        if ($request->has('active')) {
            $isActive = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $extras = $query->paginate($perPage);

        return response()->json([
            'data' => $extras->map(fn($extra) => $this->formatExtra($extra)),
            'meta' => [
                'current_page' => $extras->currentPage(),
                'last_page' => $extras->lastPage(),
                'per_page' => $extras->perPage(),
                'total' => $extras->total(),
            ],
        ]);
    }

    /**
     * Get a specific extra
     *
     * @param Extra $extra
     * @return JsonResponse
     */
    public function show(Extra $extra): JsonResponse
    {
        $extra->loadCount('solarPlantExtras');

        return response()->json([
            'data' => $this->formatExtra($extra, true),
        ]);
    }

    /**
     * Create a new extra
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Only admin can create extras
        if (!$request->user()->hasRole('admin', 'sanctum')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only administrators can create extras.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'default_price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $extra = Extra::create($validated);

        // Log activity
        $this->activityService->log('created extra', $extra, $request->user(), $validated);

        return response()->json([
            'data' => $this->formatExtra($extra),
            'meta' => [
                'status' => 'success',
                'message' => 'Extra created successfully.',
            ],
        ], 201);
    }

    /**
     * Update an existing extra
     *
     * @param Request $request
     * @param Extra $extra
     * @return JsonResponse
     */
    public function update(Request $request, Extra $extra): JsonResponse
    {
        // Only admin can update extras
        if (!$request->user()->hasRole('admin', 'sanctum')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only administrators can update extras.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'default_price' => 'sometimes|numeric|min:0',
            'unit' => 'sometimes|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $oldValues = $extra->toArray();
        $extra->update($validated);

        // Log activity
        $this->activityService->log('updated extra', $extra, $request->user(), [
            'old' => $oldValues,
            'new' => $extra->fresh()->toArray(),
        ]);

        return response()->json([
            'data' => $this->formatExtra($extra),
            'meta' => [
                'status' => 'success',
                'message' => 'Extra updated successfully.',
            ],
        ]);
    }

    /**
     * Delete an extra
     *
     * @param Request $request
     * @param Extra $extra
     * @return JsonResponse
     */
    public function destroy(Request $request, Extra $extra): JsonResponse
    {
        // Only admin can delete extras
        if (!$request->user()->hasRole('admin', 'sanctum')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only administrators can delete extras.',
            ], 403);
        }

        // Check if extra is in use
        $usageCount = $extra->solarPlantExtras()->count();
        if ($usageCount > 0) {
            return response()->json([
                'error' => 'Cannot Delete',
                'message' => "This extra is currently used by {$usageCount} solar plant(s). Please remove all assignments before deleting.",
            ], 400);
        }

        // Log activity before deletion
        $this->activityService->log('deleted extra', $extra, $request->user(), $extra->toArray());

        $extra->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Extra deleted successfully.',
            ],
        ]);
    }

    /**
     * Toggle active status of an extra
     *
     * @param Request $request
     * @param Extra $extra
     * @return JsonResponse
     */
    public function toggleActive(Request $request, Extra $extra): JsonResponse
    {
        // Only admin can toggle status
        if (!$request->user()->hasRole('admin', 'sanctum')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only administrators can change extra status.',
            ], 403);
        }

        $extra->update([
            'is_active' => !$extra->is_active,
        ]);

        $status = $extra->is_active ? 'activated' : 'deactivated';

        // Log activity
        $this->activityService->log("{$status} extra", $extra, $request->user(), ['is_active' => $extra->is_active]);

        return response()->json([
            'data' => $this->formatExtra($extra),
            'meta' => [
                'status' => 'success',
                'message' => "Extra {$status} successfully.",
            ],
        ]);
    }

    /**
     * Get usage statistics for an extra
     *
     * @param Extra $extra
     * @return JsonResponse
     */
    public function usage(Extra $extra): JsonResponse
    {
        $extra->load(['solarPlantExtras.solarPlant']);

        $plantExtras = $extra->solarPlantExtras;

        $stats = [
            'total_plants' => $plantExtras->count(),
            'total_quantity' => $plantExtras->sum('quantity'),
            'total_revenue' => $plantExtras->sum('total_cost'),
            'plants' => $plantExtras->map(function ($plantExtra) {
                return [
                    'id' => $plantExtra->solar_plant_id,
                    'plant_name' => $plantExtra->solarPlant->plant_name ?? 'Unknown',
                    'quantity' => $plantExtra->quantity,
                    'price' => $plantExtra->price,
                    'total_cost' => $plantExtra->total_cost,
                ];
            })->values(),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get all active extras (public endpoint for customers)
     *
     * @return JsonResponse
     */
    public function activeExtras(): JsonResponse
    {
        $extras = Extra::active()
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn($extra) => $this->formatExtra($extra));

        return response()->json([
            'data' => $extras,
        ]);
    }

    /**
     * Format extra for API response
     *
     * @param Extra $extra
     * @param bool $detailed
     * @return array
     */
    protected function formatExtra(Extra $extra, bool $detailed = false): array
    {
        $data = [
            'id' => $extra->id,
            'name' => $extra->name,
            'description' => $extra->description,
            'default_price' => (float) $extra->default_price,
            'unit' => $extra->unit,
            'is_active' => $extra->is_active,
            'created_at' => $extra->created_at,
            'updated_at' => $extra->updated_at,
        ];

        if ($detailed) {
            $data['usage_count'] = $extra->solar_plant_extras_count ?? $extra->solarPlantExtras()->count();
        }

        return $data;
    }
}
