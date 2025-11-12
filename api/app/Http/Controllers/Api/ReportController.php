<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get dashboard overview statistics
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = null;
        $userId = null;

        // Determine role and userId for filtering
        if ($user->hasRole('customer')) {
            $role = 'customer';
            $userId = $user->id;
        } elseif ($user->hasRole('manager')) {
            $role = 'manager';
            $userId = $user->id;
        }
        // Admin gets unfiltered data

        $overview = $this->reportService->getDashboardOverview($role, $userId);

        return response()->json([
            'data' => $overview,
        ]);
    }

    /**
     * Get investment analytics
     */
    public function investmentAnalytics(Request $request): JsonResponse
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

        $filters = [];
        if ($request->has('start_date')) {
            $filters['start_date'] = $request->start_date;
        }
        if ($request->has('end_date')) {
            $filters['end_date'] = $request->end_date;
        }

        $analytics = $this->reportService->getInvestmentAnalytics($filters);

        return response()->json([
            'data' => $analytics,
        ]);
    }

    /**
     * Get repayment analytics
     */
    public function repaymentAnalytics(Request $request): JsonResponse
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

        $filters = [];
        if ($request->has('start_date')) {
            $filters['start_date'] = $request->start_date;
        }
        if ($request->has('end_date')) {
            $filters['end_date'] = $request->end_date;
        }

        $analytics = $this->reportService->getRepaymentAnalytics($filters);

        return response()->json([
            'data' => $analytics,
        ]);
    }

    /**
     * Get solar plant analytics
     */
    public function plantAnalytics(Request $request): JsonResponse
    {
        $analytics = $this->reportService->getSolarPlantAnalytics();

        return response()->json([
            'data' => $analytics,
        ]);
    }

    /**
     * Generate monthly report
     */
    public function monthlyReport(Request $request, int $year, int $month): JsonResponse
    {
        if ($month < 1 || $month > 12) {
            return response()->json([
                'message' => 'Invalid month. Must be between 1 and 12.',
            ], 422);
        }

        if ($year < 2020 || $year > 2100) {
            return response()->json([
                'message' => 'Invalid year. Must be between 2020 and 2100.',
            ], 422);
        }

        $report = $this->reportService->generateMonthlyReport($year, $month);

        return response()->json([
            'data' => $report,
        ]);
    }

    /**
     * Get investment performance metrics
     */
    public function investmentPerformance(Request $request, Investment $investment): JsonResponse
    {
        $user = $request->user();

        // Check access
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('manager') && $investment->solarPlant->manager_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized to view this investment performance',
                ], 403);
            } elseif ($user->hasRole('customer') && $investment->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized to view this investment performance',
                ], 403);
            }
        }

        $performance = $this->reportService->getInvestmentPerformance($investment);

        return response()->json([
            'data' => $performance,
        ]);
    }

    /**
     * Export report to CSV
     */
    public function exportInvestments(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:pending,active,completed,defaulted,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Build query
        $query = Investment::with(['user', 'solarPlant'])->where('rs', 0);

        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Apply role-based filtering
        $user = $request->user();
        if ($user->hasRole('customer')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('manager')) {
            $query->whereHas('solarPlant', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }

        $investments = $query->get();

        // Generate CSV content
        $csvContent = "Investment ID,Investor Name,Solar Plant,Amount,Interest Rate,Duration (months),Status,Start Date,End Date,Created At\n";

        foreach ($investments as $investment) {
            $csvContent .= sprintf(
                "%s,%s,%s,%.2f,%.2f,%d,%s,%s,%s,%s\n",
                substr($investment->id, 0, 8),
                $investment->user->name ?? '',
                $investment->solarPlant->title ?? '',
                $investment->amount,
                $investment->interest_rate,
                $investment->duration_months,
                $investment->status,
                $investment->start_date ?? '',
                $investment->end_date ?? '',
                $investment->created_at->format('Y-m-d H:i:s')
            );
        }

        // Store temporarily
        $filename = 'investments_export_' . now()->format('Y-m-d_His') . '.csv';
        $path = 'exports/' . $filename;
        \Storage::disk('private')->put($path, $csvContent);

        // Log activity
        activity()
            ->causedBy($request->user())
            ->withProperties([
                'export_type' => 'investments',
                'record_count' => $investments->count(),
                'filters' => $request->only(['start_date', 'end_date', 'status']),
            ])
            ->log('exported investments to CSV');

        return response()->json([
            'message' => 'Export generated successfully',
            'data' => [
                'filename' => $filename,
                'path' => $path,
                'record_count' => $investments->count(),
                'download_url' => route('api.reports.download', ['filename' => $filename]),
            ],
        ]);
    }

    /**
     * Download exported file
     */
    public function downloadExport(Request $request, string $filename)
    {
        $path = 'exports/' . $filename;

        if (!\Storage::disk('private')->exists($path)) {
            return response()->json([
                'message' => 'File not found',
            ], 404);
        }

        // Log activity
        activity()
            ->causedBy($request->user())
            ->withProperties(['filename' => $filename])
            ->log('downloaded export file');

        return \Storage::disk('private')->download($path, $filename);
    }
}
