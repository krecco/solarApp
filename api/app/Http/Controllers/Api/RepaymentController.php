<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvestmentRepayment;
use App\Models\Investment;
use App\Services\ActivityService;
use App\Services\RepaymentCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RepaymentController extends Controller
{
    protected RepaymentCalculatorService $repaymentCalculator;
    protected ActivityService $activityService;

    public function __construct(RepaymentCalculatorService $repaymentCalculator, ActivityService $activityService)
    {
        $this->repaymentCalculator = $repaymentCalculator;
        $this->activityService = $activityService;
    }

    /**
     * Get repayments for a specific investment
     */
    public function index(Request $request, Investment $investment): JsonResponse
    {
        $repayments = $investment->repayments()
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'data' => $repayments,
            'summary' => [
                'total_payments' => $repayments->count(),
                'paid_count' => $repayments->where('status', 'paid')->count(),
                'pending_count' => $repayments->where('status', 'pending')->count(),
                'overdue_count' => $repayments->filter(function ($r) {
                    return $r->status === 'pending' && now()->gt($r->due_date);
                })->count(),
            ],
        ]);
    }

    /**
     * Mark a repayment as paid
     */
    public function markAsPaid(Request $request, InvestmentRepayment $repayment): JsonResponse
    {
        // Check authorization
        $user = $request->user();
        if (!$user->hasRole(['admin', 'manager'])) {
            return response()->json([
                'message' => 'Unauthorized to mark repayments as paid',
            ], 403);
        }

        if ($repayment->status === 'paid') {
            return response()->json([
                'message' => 'Repayment already marked as paid',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|in:bank_transfer,sepa,check,cash,other',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        try {
            $this->repaymentCalculator->markRepaymentAsPaid(
                $repayment,
                $data['amount'],
                $data['payment_method'] ?? null,
                $data['payment_reference'] ?? null
            );

            // Log activity
            $this->activityService->log('marked repayment as paid', $repayment, $user, [
                'investment_id' => $repayment->investment_id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? null,
            ]);

            return response()->json([
                'message' => 'Repayment marked as paid successfully',
                'data' => $repayment->fresh()->load('investment'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark repayment as paid',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get overdue repayments
     */
    public function overdue(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = InvestmentRepayment::with(['investment.user', 'investment.solarPlant'])
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc');

        // Apply role-based filtering
        if ($user->hasRole('customer', 'sanctum')) {
            $query->whereHas('investment', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->hasRole('manager', 'sanctum')) {
            $query->whereHas('investment.solarPlant', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }

        $overdueRepayments = $query->get();

        // Calculate late fees for each
        $overdueRepayments->each(function ($repayment) {
            $repayment->late_fee = $this->repaymentCalculator->calculateLateFee($repayment);
            $repayment->days_overdue = now()->diffInDays($repayment->due_date);
        });

        return response()->json([
            'data' => $overdueRepayments,
            'total' => $overdueRepayments->count(),
            'total_amount_due' => $overdueRepayments->sum('amount'),
            'total_late_fees' => $overdueRepayments->sum('late_fee'),
        ]);
    }

    /**
     * Get upcoming repayments (due in next 30 days)
     */
    public function upcoming(Request $request): JsonResponse
    {
        $user = $request->user();
        $daysAhead = $request->get('days', 30);

        $query = InvestmentRepayment::with(['investment.user', 'investment.solarPlant'])
            ->where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays($daysAhead)])
            ->orderBy('due_date', 'asc');

        // Apply role-based filtering
        if ($user->hasRole('customer', 'sanctum')) {
            $query->whereHas('investment', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->hasRole('manager', 'sanctum')) {
            $query->whereHas('investment.solarPlant', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }

        $upcomingRepayments = $query->get();

        return response()->json([
            'data' => $upcomingRepayments,
            'total' => $upcomingRepayments->count(),
            'total_amount' => $upcomingRepayments->sum('amount'),
        ]);
    }

    /**
     * Regenerate repayment schedule for an investment
     */
    public function regenerate(Request $request, Investment $investment): JsonResponse
    {
        // Only admin can regenerate
        if (!$request->user()->hasRole('admin', 'sanctum')) {
            return response()->json([
                'message' => 'Unauthorized to regenerate repayment schedule',
            ], 403);
        }

        try {
            $repaymentsCreated = $this->repaymentCalculator->recalculateRepaymentSchedule($investment);

            // Log activity
            $this->activityService->log('regenerated repayment schedule', $investment, $request->user(), ['repayments_created' => $repaymentsCreated]);

            return response()->json([
                'message' => 'Repayment schedule regenerated successfully',
                'repayments_created' => $repaymentsCreated,
                'data' => $investment->load('repayments'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to regenerate repayment schedule',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get repayment statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = InvestmentRepayment::query();

        // Apply role-based filtering
        if ($user->hasRole('customer', 'sanctum')) {
            $query->whereHas('investment', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->hasRole('manager', 'sanctum')) {
            $query->whereHas('investment.solarPlant', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }

        $statistics = [
            'total_repayments' => (clone $query)->count(),
            'paid' => (clone $query)->where('status', 'paid')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'overdue' => (clone $query)->where('status', 'pending')
                ->where('due_date', '<', now())
                ->count(),
            'total_paid_amount' => (clone $query)->where('status', 'paid')->sum('paid_amount'),
            'total_pending_amount' => (clone $query)->where('status', 'pending')->sum('amount'),
            'due_this_month' => (clone $query)->where('status', 'pending')
                ->whereBetween('due_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'due_this_month_amount' => (clone $query)->where('status', 'pending')
                ->whereBetween('due_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
        ];

        return response()->json($statistics);
    }
}
