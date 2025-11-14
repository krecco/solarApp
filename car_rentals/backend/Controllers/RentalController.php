<?php

namespace App\Modules\CarRentals\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CarRentals\Enums\RentalStatus;
use App\Modules\CarRentals\Models\Rental;
use App\Modules\CarRentals\Requests\StoreRentalRequest;
use App\Modules\CarRentals\Requests\UpdateRentalRequest;
use App\Modules\CarRentals\Resources\RentalResource;
use App\Modules\CarRentals\Services\WorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    public function __construct(
        private WorkflowService $workflowService
    ) {}

    /**
     * Display a listing of rentals
     */
    public function index(Request $request): JsonResponse
    {
        $query = Rental::with(['user', 'vehicle', 'verifiedBy', 'extras']);

        // Role-based filtering
        $user = $request->user();
        if ($user->hasRole('customer')) {
            $query->where('user_id', $user->id);
        }

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->has('user_id') && $user->hasAnyRole(['manager', 'admin'])) {
            $query->where('user_id', $request->user_id);
        }

        // Date range
        if ($request->has('from_date')) {
            $query->where('pickup_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('return_date', '<=', $request->to_date);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rental_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($userQ) => $userQ->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('vehicle', fn($vehicleQ) =>
                        $vehicleQ->where('make', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                    );
            });
        }

        $rentals = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => RentalResource::collection($rentals),
            'meta' => [
                'current_page' => $rentals->currentPage(),
                'last_page' => $rentals->lastPage(),
                'per_page' => $rentals->perPage(),
                'total' => $rentals->total(),
            ],
        ]);
    }

    /**
     * Store a newly created rental
     */
    public function store(StoreRentalRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Generate rental number
        $data['rental_number'] = Rental::generateRentalNumber();
        $data['user_id'] = $request->user()->id;
        $data['document_language'] = $request->get('document_language', app()->getLocale());

        // Calculate pricing
        $rental = new Rental($data);
        $rental->total_days = $rental->calculateTotalDays();
        $rental->subtotal = $rental->calculateSubtotal();
        $rental->tax_amount = $rental->calculateTax();
        $rental->total_amount = $rental->calculateTotal();
        $rental->save();

        // Add extras
        if ($request->has('extras')) {
            foreach ($request->extras as $extra) {
                $rental->extras()->create($extra);
            }
        }

        // Transition to pending status
        $this->workflowService->transitionRental($rental, RentalStatus::PENDING);

        return response()->json([
            'success' => true,
            'message' => 'Rental booking created successfully',
            'data' => new RentalResource($rental->load(['vehicle', 'user', 'extras'])),
        ], 201);
    }

    /**
     * Display the specified rental
     */
    public function show(Request $request, Rental $rental): JsonResponse
    {
        // Authorization check
        $user = $request->user();
        if ($user->hasRole('customer') && $rental->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $rental->load([
            'user',
            'vehicle',
            'vehicle.fileContainer',
            'verifiedBy',
            'extras',
            'payments',
            'fileContainer',
        ]);

        return response()->json([
            'success' => true,
            'data' => new RentalResource($rental),
        ]);
    }

    /**
     * Update the specified rental
     */
    public function update(UpdateRentalRequest $request, Rental $rental): JsonResponse
    {
        // Check if rental can be updated
        if (in_array($rental->status, [RentalStatus::ACTIVE, RentalStatus::COMPLETED])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update rental in current status',
            ], 422);
        }

        $rental->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Rental updated successfully',
            'data' => new RentalResource($rental->load(['vehicle', 'user', 'extras'])),
        ]);
    }

    /**
     * Verify a rental
     */
    public function verify(Request $request, Rental $rental): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string',
        ]);

        $newStatus = $request->action === 'approve'
            ? RentalStatus::VERIFIED
            : RentalStatus::REJECTED;

        if (!$this->workflowService->transitionRental($rental, $newStatus, $request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot transition to this status',
            ], 422);
        }

        if ($request->has('notes')) {
            $rental->update(['notes' => $request->notes]);
        }

        return response()->json([
            'success' => true,
            'message' => "Rental {$request->action}d successfully",
            'data' => new RentalResource($rental->fresh(['vehicle', 'user'])),
        ]);
    }

    /**
     * Check-in (pickup) vehicle
     */
    public function checkin(Request $request, Rental $rental): JsonResponse
    {
        $request->validate([
            'pickup_mileage' => 'required|numeric|min:0',
            'pickup_condition' => 'required|string',
        ]);

        $rental->update([
            'pickup_mileage' => $request->pickup_mileage,
            'pickup_condition' => $request->pickup_condition,
        ]);

        $this->workflowService->transitionRental($rental, RentalStatus::ACTIVE, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle checked in successfully',
            'data' => new RentalResource($rental->fresh(['vehicle', 'user'])),
        ]);
    }

    /**
     * Check-out (return) vehicle
     */
    public function checkout(Request $request, Rental $rental): JsonResponse
    {
        $request->validate([
            'return_mileage' => 'required|numeric|min:0',
            'return_condition' => 'required|string',
            'damage_report' => 'nullable|string',
            'damage_cost' => 'nullable|numeric|min:0',
        ]);

        $rental->update($request->only([
            'return_mileage',
            'return_condition',
            'damage_report',
            'damage_cost',
        ]));

        // Calculate excess mileage
        $rental->excess_mileage = $rental->calculateExcessMileage();
        $rental->save();

        $this->workflowService->transitionRental($rental, RentalStatus::COMPLETED, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle checked out successfully',
            'data' => new RentalResource($rental->fresh(['vehicle', 'user'])),
        ]);
    }

    /**
     * Cancel a rental
     */
    public function cancel(Request $request, Rental $rental): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        if (!$this->workflowService->transitionRental($rental, RentalStatus::CANCELLED, $request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel rental in current status',
            ], 422);
        }

        if ($request->has('reason')) {
            $rental->update(['notes' => $request->reason]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rental cancelled successfully',
            'data' => new RentalResource($rental->fresh(['vehicle', 'user'])),
        ]);
    }
}
