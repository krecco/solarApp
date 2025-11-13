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
        if ($user->hasRole('customer', 'sanctum')) {
            $role = 'customer';
            $userId = $user->id;
        } elseif ($user->hasRole('manager', 'sanctum')) {
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
        if (!$user->hasRole('admin', 'sanctum')) {
            if ($user->hasRole('manager', 'sanctum') && $investment->solarPlant->manager_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized to view this investment performance',
                ], 403);
            } elseif ($user->hasRole('customer', 'sanctum') && $investment->user_id !== $user->id) {
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
        if ($user->hasRole('customer', 'sanctum')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('manager', 'sanctum')) {
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

    /**
     * Get cohort analysis - Group investors by registration month and track retention
     */
    public function cohortAnalysis(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'period' => 'nullable|in:month,quarter,year',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $period = $request->input('period', 'month');
        $startDate = $request->input('start_date', now()->subYear()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        // Get cohort data
        $cohorts = \DB::table('users as u')
            ->selectRaw(\DB::raw("DATE_TRUNC('{$period}', u.created_at) as cohort_period"))
            ->selectRaw('COUNT(DISTINCT u.id) as user_count')
            ->selectRaw('COUNT(DISTINCT i.id) as investment_count')
            ->selectRaw('COALESCE(SUM(i.amount), 0) as total_invested')
            ->leftJoin('investments as i', function ($join) {
                $join->on('u.id', '=', 'i.user_id')
                    ->where('i.rs', '=', 0);
            })
            ->where('u.created_at', '>=', $startDate)
            ->where('u.created_at', '<=', $endDate)
            ->where('u.rs', 0)
            ->groupBy('cohort_period')
            ->orderBy('cohort_period')
            ->get();

        return response()->json([
            'data' => [
                'period' => $period,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'cohorts' => $cohorts,
            ],
        ]);
    }

    /**
     * Get time-series trend analysis
     */
    public function trendAnalysis(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'metric' => 'required|in:investments,repayments,revenue,users',
            'period' => 'nullable|in:day,week,month,quarter,year',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $metric = $request->metric;
        $period = $request->input('period', 'month');
        $startDate = $request->input('start_date', now()->subYear());
        $endDate = $request->input('end_date', now());

        $trends = [];

        switch ($metric) {
            case 'investments':
                $trends = Investment::selectRaw(\DB::raw("DATE_TRUNC('{$period}', created_at) as period"))
                    ->selectRaw('COUNT(*) as count')
                    ->selectRaw('SUM(amount) as total')
                    ->selectRaw('AVG(amount) as average')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('rs', 0)
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;

            case 'repayments':
                $trends = \App\Models\InvestmentRepayment::selectRaw(\DB::raw("DATE_TRUNC('{$period}', due_date) as period"))
                    ->selectRaw('COUNT(*) as count')
                    ->selectRaw('SUM(amount) as total')
                    ->selectRaw('SUM(CASE WHEN status = \'paid\' THEN amount ELSE 0 END) as paid')
                    ->selectRaw('SUM(CASE WHEN status = \'pending\' THEN amount ELSE 0 END) as pending')
                    ->whereBetween('due_date', [$startDate, $endDate])
                    ->where('rs', 0)
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;

            case 'revenue':
                $trends = \App\Models\InvestmentRepayment::selectRaw(\DB::raw("DATE_TRUNC('{$period}', paid_at) as period"))
                    ->selectRaw('COUNT(*) as count')
                    ->selectRaw('SUM(amount) as total')
                    ->whereNotNull('paid_at')
                    ->whereBetween('paid_at', [$startDate, $endDate])
                    ->where('rs', 0)
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;

            case 'users':
                $trends = \App\Models\User::selectRaw(\DB::raw("DATE_TRUNC('{$period}', created_at) as period"))
                    ->selectRaw('COUNT(*) as count')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('rs', 0)
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;
        }

        return response()->json([
            'data' => [
                'metric' => $metric,
                'period' => $period,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'trends' => $trends,
            ],
        ]);
    }

    /**
     * Get comparative analytics (YoY, MoM)
     */
    public function comparativeAnalysis(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:yoy,mom,qoq',
            'metric' => 'required|in:investments,revenue,users',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $type = $request->type;
        $metric = $request->metric;

        $current = null;
        $previous = null;
        $change = null;
        $changePercent = null;

        // Determine date ranges based on comparison type
        switch ($type) {
            case 'yoy': // Year-over-Year
                $currentStart = now()->startOfYear();
                $currentEnd = now();
                $previousStart = now()->subYear()->startOfYear();
                $previousEnd = now()->subYear();
                break;

            case 'mom': // Month-over-Month
                $currentStart = now()->startOfMonth();
                $currentEnd = now();
                $previousStart = now()->subMonth()->startOfMonth();
                $previousEnd = now()->subMonth()->endOfMonth();
                break;

            case 'qoq': // Quarter-over-Quarter
                $currentStart = now()->firstOfQuarter();
                $currentEnd = now();
                $previousStart = now()->subQuarter()->firstOfQuarter();
                $previousEnd = now()->subQuarter()->lastOfQuarter();
                break;
        }

        // Get metrics based on type
        switch ($metric) {
            case 'investments':
                $current = Investment::whereBetween('created_at', [$currentStart, $currentEnd])
                    ->where('rs', 0)
                    ->sum('amount');
                $previous = Investment::whereBetween('created_at', [$previousStart, $previousEnd])
                    ->where('rs', 0)
                    ->sum('amount');
                break;

            case 'revenue':
                $current = \App\Models\InvestmentRepayment::whereNotNull('paid_at')
                    ->whereBetween('paid_at', [$currentStart, $currentEnd])
                    ->where('rs', 0)
                    ->sum('amount');
                $previous = \App\Models\InvestmentRepayment::whereNotNull('paid_at')
                    ->whereBetween('paid_at', [$previousStart, $previousEnd])
                    ->where('rs', 0)
                    ->sum('amount');
                break;

            case 'users':
                $current = \App\Models\User::whereBetween('created_at', [$currentStart, $currentEnd])
                    ->where('rs', 0)
                    ->count();
                $previous = \App\Models\User::whereBetween('created_at', [$previousStart, $previousEnd])
                    ->where('rs', 0)
                    ->count();
                break;
        }

        $change = $current - $previous;
        $changePercent = $previous > 0 ? (($change / $previous) * 100) : 0;

        return response()->json([
            'data' => [
                'type' => $type,
                'metric' => $metric,
                'current' => [
                    'value' => $current,
                    'period_start' => $currentStart->format('Y-m-d'),
                    'period_end' => $currentEnd->format('Y-m-d'),
                ],
                'previous' => [
                    'value' => $previous,
                    'period_start' => $previousStart->format('Y-m-d'),
                    'period_end' => $previousEnd->format('Y-m-d'),
                ],
                'comparison' => [
                    'change' => round($change, 2),
                    'change_percent' => round($changePercent, 2),
                    'trend' => $change >= 0 ? 'up' : 'down',
                ],
            ],
        ]);
    }

    /**
     * Get multi-dimensional analytics
     */
    public function multiDimensionalAnalysis(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'dimensions' => 'required|array|min:1|max:3',
            'dimensions.*' => 'in:location,status,plant,user_type',
            'metric' => 'required|in:count,amount,average',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dimensions = $request->dimensions;
        $metric = $request->metric;

        // Build query based on dimensions
        $query = Investment::query()
            ->join('solar_plants', 'investments.solar_plant_id', '=', 'solar_plants.id')
            ->join('users', 'investments.user_id', '=', 'users.id')
            ->where('investments.rs', 0);

        $selectFields = [];
        $groupFields = [];

        foreach ($dimensions as $dimension) {
            switch ($dimension) {
                case 'location':
                    $selectFields[] = 'solar_plants.location';
                    $groupFields[] = 'solar_plants.location';
                    break;
                case 'status':
                    $selectFields[] = 'investments.status';
                    $groupFields[] = 'investments.status';
                    break;
                case 'plant':
                    $selectFields[] = 'solar_plants.title as plant_name';
                    $groupFields[] = 'solar_plants.title';
                    break;
                case 'user_type':
                    $selectFields[] = 'users.user_type';
                    $groupFields[] = 'users.user_type';
                    break;
            }
        }

        // Add metric calculation
        switch ($metric) {
            case 'count':
                $selectFields[] = \DB::raw('COUNT(investments.id) as value');
                break;
            case 'amount':
                $selectFields[] = \DB::raw('SUM(investments.amount) as value');
                break;
            case 'average':
                $selectFields[] = \DB::raw('AVG(investments.amount) as value');
                break;
        }

        $results = $query->select($selectFields)
            ->groupBy($groupFields)
            ->orderBy('value', 'desc')
            ->get();

        return response()->json([
            'data' => [
                'dimensions' => $dimensions,
                'metric' => $metric,
                'results' => $results,
            ],
        ]);
    }

    /**
     * Get financial forecast
     */
    public function forecast(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'months_ahead' => 'required|integer|min:1|max:12',
            'metric' => 'required|in:revenue,investments',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $monthsAhead = $request->months_ahead;
        $metric = $request->metric;

        // Get historical data for the past 12 months
        $historicalData = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = now()->subMonths($i)->endOfMonth();

            switch ($metric) {
                case 'revenue':
                    $value = \App\Models\InvestmentRepayment::whereNotNull('paid_at')
                        ->whereBetween('paid_at', [$start, $end])
                        ->where('rs', 0)
                        ->sum('amount');
                    break;
                case 'investments':
                    $value = Investment::whereBetween('created_at', [$start, $end])
                        ->where('rs', 0)
                        ->sum('amount');
                    break;
            }

            $historicalData[] = [
                'month' => $start->format('Y-m'),
                'value' => $value,
            ];
        }

        // Simple linear forecast based on average growth rate
        $values = array_column($historicalData, 'value');
        $avg = count($values) > 0 ? array_sum($values) / count($values) : 0;
        $trend = count($values) > 1 ? ($values[count($values) - 1] - $values[0]) / (count($values) - 1) : 0;

        $forecast = [];
        for ($i = 1; $i <= $monthsAhead; $i++) {
            $month = now()->addMonths($i)->format('Y-m');
            $predictedValue = $avg + ($trend * (count($values) + $i));
            $forecast[] = [
                'month' => $month,
                'predicted_value' => max(0, round($predictedValue, 2)),
                'confidence' => 'medium', // Simplified confidence level
            ];
        }

        return response()->json([
            'data' => [
                'metric' => $metric,
                'months_ahead' => $monthsAhead,
                'historical' => $historicalData,
                'forecast' => $forecast,
                'note' => 'Forecast based on simple linear projection. For production use, consider more sophisticated models.',
            ],
        ]);
    }

    /**
     * Export advanced report to Excel format
     */
    public function exportAdvancedReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:comprehensive,financial,operational',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'nullable|in:csv,json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $reportType = $request->report_type;
        $startDate = $request->input('start_date', now()->subMonth());
        $endDate = $request->input('end_date', now());
        $format = $request->input('format', 'csv');

        $data = [];

        switch ($reportType) {
            case 'comprehensive':
                $data = [
                    'investments' => Investment::with(['user', 'solarPlant'])
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->where('rs', 0)
                        ->get(),
                    'repayments' => \App\Models\InvestmentRepayment::with(['investment'])
                        ->whereBetween('due_date', [$startDate, $endDate])
                        ->where('rs', 0)
                        ->get(),
                    'plants' => \App\Models\SolarPlant::where('rs', 0)->get(),
                ];
                break;

            case 'financial':
                $data = [
                    'revenue' => \App\Models\InvestmentRepayment::whereNotNull('paid_at')
                        ->whereBetween('paid_at', [$startDate, $endDate])
                        ->where('rs', 0)
                        ->sum('amount'),
                    'outstanding' => \App\Models\InvestmentRepayment::where('status', 'pending')
                        ->where('rs', 0)
                        ->sum('amount'),
                    'investments' => Investment::whereBetween('created_at', [$startDate, $endDate])
                        ->where('rs', 0)
                        ->sum('amount'),
                ];
                break;

            case 'operational':
                $data = [
                    'active_users' => \App\Models\User::where('rs', 0)->count(),
                    'active_plants' => \App\Models\SolarPlant::where('status', 'operational')
                        ->where('rs', 0)
                        ->count(),
                    'pending_verifications' => \App\Models\File::where('verification_status', 'pending')
                        ->where('rs', 0)
                        ->count(),
                ];
                break;
        }

        // Generate filename
        $filename = "{$reportType}_report_" . now()->format('Y-m-d_His') . ".{$format}";
        $path = 'exports/' . $filename;

        // Store file
        if ($format === 'csv') {
            // Convert to CSV (simplified)
            $csvContent = json_encode($data);
            \Storage::disk('private')->put($path, $csvContent);
        } else {
            $jsonContent = json_encode($data, JSON_PRETTY_PRINT);
            \Storage::disk('private')->put($path, $jsonContent);
        }

        // Log activity
        activity()
            ->causedBy($request->user())
            ->withProperties([
                'report_type' => $reportType,
                'date_range' => [$startDate, $endDate],
            ])
            ->log('exported advanced report');

        return response()->json([
            'message' => 'Advanced report generated successfully',
            'data' => [
                'filename' => $filename,
                'path' => $path,
                'download_url' => route('api.reports.download', ['filename' => $filename]),
            ],
        ]);
    }
}
