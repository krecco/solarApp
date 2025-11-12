<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentRepayment;
use App\Models\SolarPlant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get dashboard overview statistics
     *
     * @param string|null $role User role for filtering
     * @param int|null $userId User ID for customer filtering
     * @return array
     */
    public function getDashboardOverview(?string $role = null, ?int $userId = null): array
    {
        $plantsQuery = SolarPlant::where('rs', 0);
        $investmentsQuery = Investment::where('rs', 0);

        // Apply role-based filtering
        if ($role === 'customer' && $userId) {
            $plantsQuery->where('user_id', $userId);
            $investmentsQuery->where('user_id', $userId);
        } elseif ($role === 'manager' && $userId) {
            $plantsQuery->where('manager_id', $userId);
            $investmentsQuery->whereHas('solarPlant', function ($q) use ($userId) {
                $q->where('manager_id', $userId);
            });
        }

        return [
            'total_plants' => $plantsQuery->count(),
            'active_plants' => (clone $plantsQuery)->where('status', 'operational')->count(),
            'total_capacity' => (clone $plantsQuery)->sum('nominal_power'),
            'total_investments' => $investmentsQuery->count(),
            'total_investment_amount' => (clone $investmentsQuery)->sum('amount'),
            'active_investments' => (clone $investmentsQuery)->where('status', 'active')->count(),
            'pending_verification' => (clone $investmentsQuery)->where('verified', false)->count(),
            'total_interest_earned' => (clone $investmentsQuery)->sum('total_interest'),
        ];
    }

    /**
     * Get investment analytics
     *
     * @param array $filters
     * @return array
     */
    public function getInvestmentAnalytics(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->startOfYear();
        $endDate = $filters['end_date'] ?? Carbon::now()->endOfYear();

        $investments = Investment::where('rs', 0)
            ->whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total_investments' => $investments->count(),
            'total_amount' => $investments->sum('amount'),
            'average_amount' => $investments->avg('amount'),
            'total_interest' => $investments->sum('total_interest'),
            'by_status' => $this->getInvestmentsByStatus($investments),
            'by_month' => $this->getInvestmentsByMonth($startDate, $endDate),
            'by_repayment_interval' => $this->getInvestmentsByInterval($investments),
            'top_investors' => $this->getTopInvestors($investments, 10),
        ];
    }

    /**
     * Get repayment analytics
     *
     * @param array $filters
     * @return array
     */
    public function getRepaymentAnalytics(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->startOfYear();
        $endDate = $filters['end_date'] ?? Carbon::now()->endOfYear();

        $repayments = InvestmentRepayment::whereBetween('due_date', [$startDate, $endDate]);

        return [
            'total_repayments' => $repayments->count(),
            'total_amount' => (clone $repayments)->sum('amount'),
            'paid_count' => (clone $repayments)->where('status', 'paid')->count(),
            'paid_amount' => (clone $repayments)->where('status', 'paid')->sum('paid_amount'),
            'pending_count' => (clone $repayments)->where('status', 'pending')->count(),
            'pending_amount' => (clone $repayments)->where('status', 'pending')->sum('amount'),
            'overdue_count' => (clone $repayments)->where('status', 'overdue')->count(),
            'overdue_amount' => (clone $repayments)->where('status', 'overdue')->sum('amount'),
            'by_month' => $this->getRepaymentsByMonth($startDate, $endDate),
        ];
    }

    /**
     * Get solar plant analytics
     *
     * @param array $filters
     * @return array
     */
    public function getSolarPlantAnalytics(array $filters = []): array
    {
        $plants = SolarPlant::where('rs', 0);

        return [
            'total_plants' => $plants->count(),
            'total_capacity' => (clone $plants)->sum('nominal_power'),
            'average_capacity' => (clone $plants)->avg('nominal_power'),
            'total_cost' => (clone $plants)->sum('total_cost'),
            'by_status' => $this->getPlantsByStatus($plants),
            'expected_production' => (clone $plants)->sum('expected_annual_production'),
            'funding_statistics' => $this->getFundingStatistics($plants),
        ];
    }

    /**
     * Generate monthly investment report
     *
     * @param int $year
     * @param int $month
     * @return array
     */
    public function generateMonthlyReport(int $year, int $month): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $investments = Investment::where('rs', 0)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $repayments = InvestmentRepayment::whereBetween('due_date', [$startDate, $endDate]);

        $plants = SolarPlant::where('rs', 0)
            ->whereBetween('created_at', [$startDate, $endDate]);

        return [
            'period' => [
                'year' => $year,
                'month' => $month,
                'month_name' => $startDate->format('F'),
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'investments' => [
                'new_count' => $investments->count(),
                'new_amount' => $investments->sum('amount'),
                'verified_count' => (clone $investments)->where('verified', true)->count(),
            ],
            'repayments' => [
                'due_count' => $repayments->count(),
                'due_amount' => (clone $repayments)->sum('amount'),
                'paid_count' => (clone $repayments)->where('status', 'paid')->count(),
                'paid_amount' => (clone $repayments)->where('status', 'paid')->sum('paid_amount'),
            ],
            'plants' => [
                'new_count' => $plants->count(),
                'new_capacity' => $plants->sum('nominal_power'),
            ],
        ];
    }

    /**
     * Get investment performance metrics
     *
     * @param Investment $investment
     * @return array
     */
    public function getInvestmentPerformance(Investment $investment): array
    {
        $totalRepayments = $investment->repayments->count();
        $paidRepayments = $investment->repayments->where('status', 'paid')->count();
        $overdueRepayments = $investment->repayments->where('status', 'overdue')->count();

        $daysActive = $investment->start_date
            ? Carbon::parse($investment->start_date)->diffInDays(now())
            : 0;

        $expectedRepayments = $investment->start_date
            ? $this->calculateExpectedRepayments($investment, Carbon::parse($investment->start_date), now())
            : 0;

        return [
            'completion_percentage' => $totalRepayments > 0 ? ($paidRepayments / $totalRepayments) * 100 : 0,
            'on_time_percentage' => $expectedRepayments > 0 ? (($paidRepayments / $expectedRepayments) * 100) : 100,
            'total_paid' => $investment->paid_amount,
            'remaining_balance' => $investment->total_repayment - $investment->paid_amount,
            'paid_repayments' => $paidRepayments,
            'pending_repayments' => $totalRepayments - $paidRepayments - $overdueRepayments,
            'overdue_repayments' => $overdueRepayments,
            'days_active' => $daysActive,
            'expected_repayments' => $expectedRepayments,
            'performance_score' => $this->calculatePerformanceScore($investment),
        ];
    }

    /**
     * Get investments by status
     */
    protected function getInvestmentsByStatus($query): array
    {
        return DB::table(DB::raw("({$query->toSql()}) as investments"))
            ->mergeBindings($query->getQuery())
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->toArray();
    }

    /**
     * Get investments by month
     */
    protected function getInvestmentsByMonth(Carbon $startDate, Carbon $endDate): array
    {
        $investments = Investment::where('rs', 0)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->toArray();

        return $investments;
    }

    /**
     * Get investments by repayment interval
     */
    protected function getInvestmentsByInterval($query): array
    {
        return DB::table(DB::raw("({$query->toSql()}) as investments"))
            ->mergeBindings($query->getQuery())
            ->select('repayment_interval', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
            ->groupBy('repayment_interval')
            ->get()
            ->keyBy('repayment_interval')
            ->toArray();
    }

    /**
     * Get top investors
     */
    protected function getTopInvestors($query, int $limit = 10): array
    {
        return DB::table(DB::raw("({$query->toSql()}) as investments"))
            ->mergeBindings($query->getQuery())
            ->join('users', 'investments.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('count(*) as investment_count'),
                DB::raw('sum(investments.amount) as total_invested')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_invested')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get repayments by month
     */
    protected function getRepaymentsByMonth(Carbon $startDate, Carbon $endDate): array
    {
        return InvestmentRepayment::whereBetween('due_date', [$startDate, $endDate])
            ->select(
                DB::raw('YEAR(due_date) as year'),
                DB::raw('MONTH(due_date) as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid_count'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN paid_amount ELSE 0 END) as paid_amount')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    /**
     * Get plants by status
     */
    protected function getPlantsByStatus($query): array
    {
        return DB::table(DB::raw("({$query->toSql()}) as plants"))
            ->mergeBindings($query->getQuery())
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(nominal_power) as total_power'))
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->toArray();
    }

    /**
     * Get funding statistics
     */
    protected function getFundingStatistics($query): array
    {
        $plants = $query->get();

        $totalFundingGoal = $plants->sum('funding_goal');
        $totalCurrentFunding = $plants->sum('current_funding');
        $fullyFundedCount = $plants->where('current_funding', '>=', 'funding_goal')->count();

        return [
            'total_funding_goal' => $totalFundingGoal,
            'total_current_funding' => $totalCurrentFunding,
            'funding_percentage' => $totalFundingGoal > 0 ? ($totalCurrentFunding / $totalFundingGoal) * 100 : 0,
            'fully_funded_count' => $fullyFundedCount,
            'partially_funded_count' => $plants->where('current_funding', '>', 0)->where('current_funding', '<', 'funding_goal')->count(),
            'unfunded_count' => $plants->where('current_funding', 0)->count(),
        ];
    }

    /**
     * Calculate expected repayments based on start date and current date
     */
    protected function calculateExpectedRepayments(Investment $investment, Carbon $startDate, Carbon $currentDate): int
    {
        $interval = $investment->repayment_interval;
        $monthsPassed = $startDate->diffInMonths($currentDate);

        switch ($interval) {
            case 'monthly':
                return $monthsPassed;
            case 'quarterly':
                return (int) floor($monthsPassed / 3);
            case 'annually':
                return (int) floor($monthsPassed / 12);
            default:
                return 0;
        }
    }

    /**
     * Calculate performance score (0-100)
     */
    protected function calculatePerformanceScore(Investment $investment): float
    {
        $totalRepayments = $investment->repayments->count();
        if ($totalRepayments === 0) return 100.0;

        $paidRepayments = $investment->repayments->where('status', 'paid')->count();
        $overdueRepayments = $investment->repayments->where('status', 'overdue')->count();

        // Base score on paid percentage
        $paidScore = ($paidRepayments / $totalRepayments) * 70;

        // Penalty for overdue payments
        $overduePenalty = ($overdueRepayments / $totalRepayments) * 30;

        // Bonus for being fully paid
        $completionBonus = ($paidRepayments === $totalRepayments) ? 30 : 0;

        return min(100, $paidScore - $overduePenalty + $completionBonus);
    }
}
