<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\InvestmentVerifiedMail;
use App\Models\Investment;
use App\Models\SolarPlant;
use App\Services\RepaymentCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class InvestmentController extends Controller
{
    protected RepaymentCalculatorService $repaymentCalculator;

    public function __construct(RepaymentCalculatorService $repaymentCalculator)
    {
        $this->repaymentCalculator = $repaymentCalculator;
    }

    /**
     * Display a listing of investments.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Investment::with(['user', 'solarPlant', 'verifiedBy'])
            ->where('rs', 0);

        // Role-based filtering
        $user = $request->user();
        if ($user->hasRole('customer')) {
            // Customers only see their own investments
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('manager')) {
            // Managers see investments for plants they manage
            $query->whereHas('solarPlant', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }
        // Admins see all investments

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('verified')) {
            $query->where('verified', $request->boolean('verified'));
        }

        if ($request->has('solar_plant_id')) {
            $query->where('solar_plant_id', $request->solar_plant_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $investments = $query->paginate($perPage);

        return response()->json($investments);
    }

    /**
     * Store a newly created investment.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'solar_plant_id' => 'required|exists:solar_plants,id',
            'amount' => 'required|numeric|min:100',
            'duration_months' => 'required|integer|min:1|max:360',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'repayment_interval' => 'required|string|in:monthly,quarterly,annually',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'pending';

        // Calculate total repayment and interest
        $principal = $data['amount'];
        $rate = $data['interest_rate'] / 100;
        $months = $data['duration_months'];

        // Simple interest calculation
        $totalInterest = $principal * $rate * ($months / 12);
        $totalRepayment = $principal + $totalInterest;

        $data['total_interest'] = $totalInterest;
        $data['total_repayment'] = $totalRepayment;

        $investment = Investment::create($data);

        // Log activity
        activity()
            ->performedOn($investment)
            ->causedBy($request->user())
            ->log('created investment');

        return response()->json([
            'message' => 'Investment created successfully',
            'data' => $investment->load(['user', 'solarPlant']),
        ], 201);
    }

    /**
     * Display the specified investment.
     */
    public function show(Request $request, Investment $investment): JsonResponse
    {
        // Authorization check
        $user = $request->user();
        if ($user->hasRole('customer') && $investment->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($user->hasRole('manager') && $investment->solarPlant->manager_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $investment->load([
            'user',
            'solarPlant',
            'verifiedBy',
            'repayments',
            'fileContainer.files',
        ]);

        return response()->json(['data' => $investment]);
    }

    /**
     * Update the specified investment.
     */
    public function update(Request $request, Investment $investment): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|required|numeric|min:100',
            'duration_months' => 'sometimes|required|integer|min:1|max:360',
            'interest_rate' => 'sometimes|required|numeric|min:0|max:100',
            'repayment_interval' => 'sometimes|required|string|in:monthly,quarterly,annually',
            'status' => 'sometimes|required|string|in:pending,verified,active,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Recalculate if amount, rate, or duration changed
        if (isset($data['amount']) || isset($data['interest_rate']) || isset($data['duration_months'])) {
            $principal = $data['amount'] ?? $investment->amount;
            $rate = ($data['interest_rate'] ?? $investment->interest_rate) / 100;
            $months = $data['duration_months'] ?? $investment->duration_months;

            $totalInterest = $principal * $rate * ($months / 12);
            $totalRepayment = $principal + $totalInterest;

            $data['total_interest'] = $totalInterest;
            $data['total_repayment'] = $totalRepayment;
        }

        $oldValues = $investment->toArray();
        $investment->update($data);

        // Log activity
        activity()
            ->performedOn($investment)
            ->causedBy($request->user())
            ->withProperties([
                'old' => $oldValues,
                'new' => $investment->toArray(),
            ])
            ->log('updated investment');

        return response()->json([
            'message' => 'Investment updated successfully',
            'data' => $investment->load(['user', 'solarPlant']),
        ]);
    }

    /**
     * Remove the specified investment.
     */
    public function destroy(Request $request, Investment $investment): JsonResponse
    {
        // Check if there are paid repayments
        if ($investment->repayments()->where('status', 'paid')->exists()) {
            return response()->json([
                'message' => 'Cannot delete investment with paid repayments',
            ], 422);
        }

        // Soft delete
        $investment->rs = 99;
        $investment->save();
        $investment->delete();

        // Log activity
        activity()
            ->performedOn($investment)
            ->causedBy($request->user())
            ->log('deleted investment');

        return response()->json([
            'message' => 'Investment deleted successfully',
        ]);
    }

    /**
     * Verify an investment.
     */
    public function verify(Request $request, Investment $investment): JsonResponse
    {
        if ($investment->verified) {
            return response()->json([
                'message' => 'Investment already verified',
            ], 422);
        }

        // Set start date to today if not set
        if (!$investment->start_date) {
            $investment->start_date = now();
        }

        // Calculate end date
        $endDate = now()->addMonths($investment->duration_months);

        $investment->update([
            'verified' => true,
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
            'status' => 'active',
            'start_date' => $investment->start_date,
            'end_date' => $endDate,
        ]);

        // Generate repayment schedule
        try {
            $repaymentsCreated = $this->repaymentCalculator->createRepaymentSchedule($investment);

            // Log activity
            activity()
                ->performedOn($investment)
                ->causedBy($request->user())
                ->withProperties([
                    'repayments_created' => $repaymentsCreated,
                ])
                ->log('verified investment and generated repayment schedule');
        } catch (\Exception $e) {
            // Log error but don't fail verification
            activity()
                ->performedOn($investment)
                ->causedBy($request->user())
                ->withProperties(['error' => $e->getMessage()])
                ->log('verified investment but failed to generate repayment schedule');
        }

        // Send notification to investor
        try {
            Mail::to($investment->user->email)->send(new InvestmentVerifiedMail($investment));
        } catch (\Exception $e) {
            // Log error but don't fail verification
            activity()
                ->performedOn($investment)
                ->causedBy($request->user())
                ->withProperties(['error' => $e->getMessage()])
                ->log('verified investment but failed to send email notification');
        }

        return response()->json([
            'message' => 'Investment verified successfully',
            'data' => $investment->load(['user', 'solarPlant', 'verifiedBy', 'repayments']),
        ]);
    }

    /**
     * Get investment statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Investment::where('rs', 0);

        // Apply role-based filtering
        if ($user->hasRole('customer')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('manager')) {
            $query->whereHas('solarPlant', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }

        $statistics = [
            'total' => $query->count(),
            'by_status' => [
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'verified' => (clone $query)->where('status', 'verified')->count(),
                'active' => (clone $query)->where('status', 'active')->count(),
                'completed' => (clone $query)->where('status', 'completed')->count(),
            ],
            'total_amount' => (clone $query)->sum('amount'),
            'total_interest' => (clone $query)->sum('total_interest'),
            'verified_count' => (clone $query)->where('verified', true)->count(),
            'unverified_count' => (clone $query)->where('verified', false)->count(),
        ];

        return response()->json($statistics);
    }
}
