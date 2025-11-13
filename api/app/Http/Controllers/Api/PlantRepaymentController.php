<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolarPlant;
use App\Models\SolarPlantRepaymentData;
use App\Models\SolarPlantRepaymentLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Plant Repayment Controller
 *
 * Manages solar plant repayment tracking, reporting, and analytics.
 * Handles both repayment schedules (data) and actual payment logs.
 */
class PlantRepaymentController extends Controller
{
    /**
     * Get list of plant repayments with filtering
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = SolarPlantRepaymentData::with(['solarPlant']);

        // Filter by solar plant
        if ($request->has('solar_plant_id')) {
            $query->where('solar_plant_id', $request->input('solar_plant_id'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by repayment type
        if ($request->has('repayment_type')) {
            $query->where('repayment_type', $request->input('repayment_type'));
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('due_date', '>=', $request->input('from_date'));
        }
        if ($request->has('to_date')) {
            $query->where('due_date', '<=', $request->input('to_date'));
        }

        // Search by plant name
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('solarPlant', function ($q) use ($search) {
                $q->where('plant_name', 'ILIKE', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'due_date');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 20);
        $repayments = $query->paginate($perPage);

        return response()->json([
            'data' => $repayments->map(fn($r) => $this->formatRepayment($r)),
            'meta' => [
                'current_page' => $repayments->currentPage(),
                'last_page' => $repayments->lastPage(),
                'per_page' => $repayments->perPage(),
                'total' => $repayments->total(),
            ],
        ]);
    }

    /**
     * Get a specific plant repayment with details
     *
     * @param SolarPlantRepaymentData $repayment
     * @return JsonResponse
     */
    public function show(SolarPlantRepaymentData $repayment): JsonResponse
    {
        $repayment->load(['solarPlant.user', 'solarPlant.investments']);

        // Get payment logs for this repayment
        $logs = SolarPlantRepaymentLog::where('solar_plant_repayment_data_id', $repayment->id)
            ->orderBy('payment_date', 'desc')
            ->get();

        return response()->json([
            'data' => [
                'repayment' => $this->formatRepayment($repayment, true),
                'payment_logs' => $logs->map(fn($log) => $this->formatPaymentLog($log)),
                'solar_plant' => [
                    'id' => $repayment->solarPlant->id,
                    'plant_name' => $repayment->solarPlant->plant_name,
                    'location' => $repayment->solarPlant->location,
                    'capacity_kwp' => (float) $repayment->solarPlant->capacity_kwp,
                    'owner' => [
                        'id' => $repayment->solarPlant->user->id,
                        'name' => $repayment->solarPlant->user->name,
                        'email' => $repayment->solarPlant->user->email,
                    ],
                    'total_investments' => $repayment->solarPlant->investments->count(),
                    'total_invested' => (float) $repayment->solarPlant->investments->sum('amount'),
                ],
            ],
        ]);
    }

    /**
     * Get statistics for plant repayments
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = SolarPlantRepaymentData::query();

        // Apply solar plant filter if provided
        if ($request->has('solar_plant_id')) {
            $query->where('solar_plant_id', $request->input('solar_plant_id'));
        }

        // Apply date range filter if provided
        if ($request->has('from_date')) {
            $query->where('due_date', '>=', $request->input('from_date'));
        }
        if ($request->has('to_date')) {
            $query->where('due_date', '<=', $request->input('to_date'));
        }

        $stats = [
            'total_repayments' => $query->count(),
            'total_amount_due' => (float) $query->sum('amount'),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'paid_count' => (clone $query)->where('status', 'paid')->count(),
            'overdue_count' => (clone $query)->where('status', 'overdue')->count(),
            'pending_amount' => (float) (clone $query)->where('status', 'pending')->sum('amount'),
            'paid_amount' => (float) (clone $query)->where('status', 'paid')->sum('amount'),
            'overdue_amount' => (float) (clone $query)->where('status', 'overdue')->sum('amount'),
        ];

        // Get monthly breakdown for current year
        $monthlyStats = SolarPlantRepaymentData::select(
            DB::raw('EXTRACT(MONTH FROM due_date) as month'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->whereYear('due_date', date('Y'))
            ->groupBy(DB::raw('EXTRACT(MONTH FROM due_date)'))
            ->orderBy('month')
            ->get();

        $stats['monthly_breakdown'] = $monthlyStats->map(function ($stat) {
            return [
                'month' => (int) $stat->month,
                'count' => $stat->count,
                'total_amount' => (float) $stat->total_amount,
            ];
        });

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get repayments for a specific solar plant
     *
     * @param Request $request
     * @param SolarPlant $solarPlant
     * @return JsonResponse
     */
    public function forPlant(Request $request, SolarPlant $solarPlant): JsonResponse
    {
        $repayments = $solarPlant->repaymentData()
            ->orderBy('due_date', 'asc')
            ->get();

        $logs = $solarPlant->repaymentLogs()
            ->orderBy('payment_date', 'desc')
            ->get();

        $stats = [
            'total_scheduled' => $repayments->count(),
            'total_amount_scheduled' => (float) $repayments->sum('amount'),
            'paid_count' => $repayments->where('status', 'paid')->count(),
            'paid_amount' => (float) $repayments->where('status', 'paid')->sum('amount'),
            'pending_count' => $repayments->where('status', 'pending')->count(),
            'pending_amount' => (float) $repayments->where('status', 'pending')->sum('amount'),
            'overdue_count' => $repayments->where('status', 'overdue')->count(),
            'overdue_amount' => (float) $repayments->where('status', 'overdue')->sum('amount'),
            'total_payments_made' => $logs->count(),
            'total_amount_paid' => (float) $logs->sum('amount'),
        ];

        return response()->json([
            'data' => [
                'solar_plant' => [
                    'id' => $solarPlant->id,
                    'plant_name' => $solarPlant->plant_name,
                    'location' => $solarPlant->location,
                ],
                'statistics' => $stats,
                'repayments' => $repayments->map(fn($r) => $this->formatRepayment($r)),
                'payment_logs' => $logs->map(fn($log) => $this->formatPaymentLog($log)),
            ],
        ]);
    }

    /**
     * Generate repayment report
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'solar_plant_id' => 'sometimes|uuid|exists:solar_plants,id',
            'from_date' => 'sometimes|date',
            'to_date' => 'sometimes|date|after_or_equal:from_date',
            'status' => 'sometimes|in:pending,paid,overdue,cancelled',
            'format' => 'sometimes|in:json,csv,pdf',
        ]);

        $query = SolarPlantRepaymentData::with(['solarPlant']);

        // Apply filters
        if (isset($validated['solar_plant_id'])) {
            $query->where('solar_plant_id', $validated['solar_plant_id']);
        }
        if (isset($validated['from_date'])) {
            $query->where('due_date', '>=', $validated['from_date']);
        }
        if (isset($validated['to_date'])) {
            $query->where('due_date', '<=', $validated['to_date']);
        }
        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $repayments = $query->orderBy('due_date', 'asc')->get();

        $report = [
            'generated_at' => now()->toIso8601String(),
            'filters' => $validated,
            'summary' => [
                'total_repayments' => $repayments->count(),
                'total_amount' => (float) $repayments->sum('amount'),
                'by_status' => [
                    'pending' => [
                        'count' => $repayments->where('status', 'pending')->count(),
                        'amount' => (float) $repayments->where('status', 'pending')->sum('amount'),
                    ],
                    'paid' => [
                        'count' => $repayments->where('status', 'paid')->count(),
                        'amount' => (float) $repayments->where('status', 'paid')->sum('amount'),
                    ],
                    'overdue' => [
                        'count' => $repayments->where('status', 'overdue')->count(),
                        'amount' => (float) $repayments->where('status', 'overdue')->sum('amount'),
                    ],
                ],
                'by_plant' => $repayments->groupBy('solar_plant_id')->map(function ($plantRepayments) {
                    $plant = $plantRepayments->first()->solarPlant;
                    return [
                        'plant_id' => $plant->id,
                        'plant_name' => $plant->plant_name,
                        'count' => $plantRepayments->count(),
                        'total_amount' => (float) $plantRepayments->sum('amount'),
                    ];
                })->values(),
            ],
            'repayments' => $repayments->map(fn($r) => $this->formatRepayment($r, true)),
        ];

        // Log activity
        activity()
            ->causedBy($request->user())
            ->withProperties($validated)
            ->log('generated plant repayment report');

        return response()->json([
            'data' => $report,
        ]);
    }

    /**
     * Record a payment log
     *
     * @param Request $request
     * @param SolarPlantRepaymentData $repayment
     * @return JsonResponse
     */
    public function recordPayment(Request $request, SolarPlantRepaymentData $repayment): JsonResponse
    {
        // Only admin/manager can record payments
        if (!$request->user()->hasRole(['admin', 'manager'])) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only administrators and managers can record payments.',
            ], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:100',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $log = SolarPlantRepaymentLog::create([
            'solar_plant_id' => $repayment->solar_plant_id,
            'solar_plant_repayment_data_id' => $repayment->id,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update repayment status if fully paid
        $totalPaid = SolarPlantRepaymentLog::where('solar_plant_repayment_data_id', $repayment->id)
            ->sum('amount');

        if ($totalPaid >= $repayment->amount) {
            $repayment->update(['status' => 'paid']);
        }

        // Log activity
        activity()
            ->performedOn($log)
            ->causedBy($request->user())
            ->withProperties($validated)
            ->log('recorded plant repayment payment');

        return response()->json([
            'data' => $this->formatPaymentLog($log),
            'meta' => [
                'status' => 'success',
                'message' => 'Payment recorded successfully.',
                'repayment_status' => $repayment->fresh()->status,
            ],
        ], 201);
    }

    /**
     * Format repayment for API response
     *
     * @param SolarPlantRepaymentData $repayment
     * @param bool $detailed
     * @return array
     */
    protected function formatRepayment(SolarPlantRepaymentData $repayment, bool $detailed = false): array
    {
        $data = [
            'id' => $repayment->id,
            'solar_plant_id' => $repayment->solar_plant_id,
            'solar_plant_name' => $repayment->solarPlant->plant_name ?? 'Unknown',
            'amount' => (float) $repayment->amount,
            'due_date' => $repayment->due_date,
            'repayment_type' => $repayment->repayment_type,
            'status' => $repayment->status,
            'created_at' => $repayment->created_at,
            'updated_at' => $repayment->updated_at,
        ];

        if ($detailed) {
            // Get payment logs
            $logs = SolarPlantRepaymentLog::where('solar_plant_repayment_data_id', $repayment->id)->get();
            $totalPaid = $logs->sum('amount');

            $data['payment_summary'] = [
                'total_paid' => (float) $totalPaid,
                'remaining' => (float) ($repayment->amount - $totalPaid),
                'payment_count' => $logs->count(),
            ];
        }

        return $data;
    }

    /**
     * Format payment log for API response
     *
     * @param SolarPlantRepaymentLog $log
     * @return array
     */
    protected function formatPaymentLog(SolarPlantRepaymentLog $log): array
    {
        return [
            'id' => $log->id,
            'solar_plant_id' => $log->solar_plant_id,
            'repayment_data_id' => $log->solar_plant_repayment_data_id,
            'amount' => (float) $log->amount,
            'payment_date' => $log->payment_date,
            'payment_method' => $log->payment_method,
            'reference_number' => $log->reference_number,
            'notes' => $log->notes,
            'created_at' => $log->created_at,
        ];
    }
}
