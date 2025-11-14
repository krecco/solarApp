<?php

namespace App\Modules\CarRentals\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CarRentals\Enums\VehicleStatus;
use App\Modules\CarRentals\Models\Vehicle;
use App\Modules\CarRentals\Requests\StoreVehicleRequest;
use App\Modules\CarRentals\Requests\UpdateVehicleRequest;
use App\Modules\CarRentals\Resources\VehicleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of vehicles
     */
    public function index(Request $request): JsonResponse
    {
        $query = Vehicle::with(['owner', 'manager', 'fileContainer'])
            ->withCount('reviews')
            ->withAvg('reviews as average_rating', 'rating');

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('location')) {
            $query->where('location', $request->location);
        }

        if ($request->has('available_from') && $request->has('available_to')) {
            // Check availability
            $query->whereDoesntHave('rentals', function ($q) use ($request) {
                $q->where(function ($subQ) use ($request) {
                    $subQ->whereBetween('pickup_date', [$request->available_from, $request->available_to])
                        ->orWhereBetween('return_date', [$request->available_from, $request->available_to])
                        ->orWhere(function ($dateQ) use ($request) {
                            $dateQ->where('pickup_date', '<=', $request->available_from)
                                ->where('return_date', '>=', $request->available_to);
                        });
                })->whereIn('status', ['confirmed', 'active']);
            });
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('license_plate', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $vehicles = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => VehicleResource::collection($vehicles),
            'meta' => [
                'current_page' => $vehicles->currentPage(),
                'last_page' => $vehicles->lastPage(),
                'per_page' => $vehicles->perPage(),
                'total' => $vehicles->total(),
            ],
        ]);
    }

    /**
     * Store a newly created vehicle
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicle = Vehicle::create($request->validated());

        // Handle multilanguage data
        if ($request->has('translations')) {
            foreach ($request->translations as $locale => $translations) {
                foreach ($translations as $field => $value) {
                    $vehicle->setTranslation($field, $value, $locale);
                }
            }
            $vehicle->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Vehicle created successfully',
            'data' => new VehicleResource($vehicle->load(['owner', 'manager'])),
        ], 201);
    }

    /**
     * Display the specified vehicle
     */
    public function show(Vehicle $vehicle): JsonResponse
    {
        $vehicle->load([
            'owner',
            'manager',
            'fileContainer',
            'reviews' => fn($q) => $q->published()->latest()->limit(10),
            'reviews.user',
            'maintenance' => fn($q) => $q->latest()->limit(5),
        ]);

        return response()->json([
            'success' => true,
            'data' => new VehicleResource($vehicle),
        ]);
    }

    /**
     * Update the specified vehicle
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        $vehicle->update($request->validated());

        // Handle multilanguage data
        if ($request->has('translations')) {
            foreach ($request->translations as $locale => $translations) {
                foreach ($translations as $field => $value) {
                    $vehicle->setTranslation($field, $value, $locale);
                }
            }
            $vehicle->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully',
            'data' => new VehicleResource($vehicle->load(['owner', 'manager'])),
        ]);
    }

    /**
     * Remove the specified vehicle
     */
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        // Check if vehicle has active rentals
        if ($vehicle->rentals()->whereIn('status', ['confirmed', 'active'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete vehicle with active rentals',
            ], 422);
        }

        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully',
        ]);
    }

    /**
     * Get vehicle availability calendar
     */
    public function availability(Vehicle $vehicle, Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $rentals = $vehicle->rentals()
            ->whereIn('status', ['confirmed', 'active'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('pickup_date', [$startDate, $endDate])
                    ->orWhereBetween('return_date', [$startDate, $endDate])
                    ->orWhere(function ($dateQ) use ($startDate, $endDate) {
                        $dateQ->where('pickup_date', '<=', $startDate)
                            ->where('return_date', '>=', $endDate);
                    });
            })
            ->get(['pickup_date', 'return_date', 'status']);

        return response()->json([
            'success' => true,
            'data' => [
                'vehicle_id' => $vehicle->id,
                'status' => $vehicle->status,
                'rentals' => $rentals,
            ],
        ]);
    }
}
