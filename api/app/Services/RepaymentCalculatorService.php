<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentRepayment;
use Carbon\Carbon;

class RepaymentCalculatorService
{
    /**
     * Calculate total repayment amount for an investment
     *
     * @param float $principal Investment amount
     * @param float $interestRate Annual interest rate (percentage)
     * @param int $durationMonths Duration in months
     * @return array ['total' => float, 'interest' => float, 'principal' => float]
     */
    public function calculateTotalRepayment(float $principal, float $interestRate, int $durationMonths): array
    {
        $rate = $interestRate / 100;
        $years = $durationMonths / 12;

        // Simple interest calculation: Principal * Rate * Time
        $totalInterest = $principal * $rate * $years;
        $totalRepayment = $principal + $totalInterest;

        return [
            'principal' => $principal,
            'interest' => $totalInterest,
            'total' => $totalRepayment,
        ];
    }

    /**
     * Generate repayment schedule for an investment
     *
     * @param Investment $investment
     * @return array Array of repayment data
     */
    public function generateRepaymentSchedule(Investment $investment): array
    {
        $schedule = [];

        // Calculate totals if not already set
        if (!$investment->total_repayment || !$investment->total_interest) {
            $totals = $this->calculateTotalRepayment(
                $investment->amount,
                $investment->interest_rate,
                $investment->duration_months
            );
            $investment->total_interest = $totals['interest'];
            $investment->total_repayment = $totals['total'];
        }

        // Determine number of payments based on interval
        $numberOfPayments = $this->getNumberOfPayments(
            $investment->duration_months,
            $investment->repayment_interval
        );

        // Calculate payment amount
        $paymentAmount = $investment->total_repayment / $numberOfPayments;
        $principalPerPayment = $investment->amount / $numberOfPayments;
        $interestPerPayment = $investment->total_interest / $numberOfPayments;

        // Determine start date (use start_date if set, otherwise use created_at)
        $startDate = $investment->start_date
            ? Carbon::parse($investment->start_date)
            : Carbon::parse($investment->created_at);

        // Generate individual repayment entries
        for ($i = 0; $i < $numberOfPayments; $i++) {
            $dueDate = $this->calculateDueDate($startDate, $i, $investment->repayment_interval);

            // Round to 2 decimal places, adjust last payment for rounding differences
            $principal = $i === $numberOfPayments - 1
                ? $investment->amount - ($principalPerPayment * ($numberOfPayments - 1))
                : round($principalPerPayment, 2);

            $interest = $i === $numberOfPayments - 1
                ? $investment->total_interest - ($interestPerPayment * ($numberOfPayments - 1))
                : round($interestPerPayment, 2);

            $amount = round($principal + $interest, 2);

            $schedule[] = [
                'investment_id' => $investment->id,
                'due_date' => $dueDate->toDateString(),
                'amount' => $amount,
                'principal_amount' => $principal,
                'interest_amount' => $interest,
                'status' => 'pending',
                'payment_number' => $i + 1,
                'total_payments' => $numberOfPayments,
            ];
        }

        return $schedule;
    }

    /**
     * Create repayment schedule in database
     *
     * @param Investment $investment
     * @return int Number of repayments created
     */
    public function createRepaymentSchedule(Investment $investment): int
    {
        // Delete existing repayments if any
        $investment->repayments()->delete();

        $schedule = $this->generateRepaymentSchedule($investment);

        // Bulk insert repayments
        foreach ($schedule as $repayment) {
            InvestmentRepayment::create($repayment);
        }

        return count($schedule);
    }

    /**
     * Get number of payments based on duration and interval
     *
     * @param int $durationMonths
     * @param string $interval monthly|quarterly|annually
     * @return int
     */
    protected function getNumberOfPayments(int $durationMonths, string $interval): int
    {
        switch ($interval) {
            case 'monthly':
                return $durationMonths;
            case 'quarterly':
                return (int) ceil($durationMonths / 3);
            case 'annually':
                return (int) ceil($durationMonths / 12);
            default:
                return $durationMonths;
        }
    }

    /**
     * Calculate due date for a specific payment
     *
     * @param Carbon $startDate
     * @param int $paymentIndex
     * @param string $interval
     * @return Carbon
     */
    protected function calculateDueDate(Carbon $startDate, int $paymentIndex, string $interval): Carbon
    {
        $dueDate = $startDate->copy();

        switch ($interval) {
            case 'monthly':
                $dueDate->addMonths($paymentIndex + 1);
                break;
            case 'quarterly':
                $dueDate->addMonths(($paymentIndex + 1) * 3);
                break;
            case 'annually':
                $dueDate->addYears($paymentIndex + 1);
                break;
        }

        return $dueDate;
    }

    /**
     * Recalculate repayment schedule for existing investment
     * Useful when investment terms are modified
     *
     * @param Investment $investment
     * @return int Number of repayments created
     */
    public function recalculateRepaymentSchedule(Investment $investment): int
    {
        // Only recalculate if no payments have been made
        $paidRepayments = $investment->repayments()->where('status', 'paid')->count();

        if ($paidRepayments > 0) {
            throw new \Exception('Cannot recalculate schedule for investment with paid repayments');
        }

        return $this->createRepaymentSchedule($investment);
    }

    /**
     * Calculate remaining balance for an investment
     *
     * @param Investment $investment
     * @return float
     */
    public function calculateRemainingBalance(Investment $investment): float
    {
        return $investment->total_repayment - $investment->paid_amount;
    }

    /**
     * Calculate next payment due date
     *
     * @param Investment $investment
     * @return Carbon|null
     */
    public function getNextPaymentDueDate(Investment $investment): ?Carbon
    {
        $nextRepayment = $investment->repayments()
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->first();

        return $nextRepayment ? Carbon::parse($nextRepayment->due_date) : null;
    }

    /**
     * Calculate completion percentage
     *
     * @param Investment $investment
     * @return float
     */
    public function calculateCompletionPercentage(Investment $investment): float
    {
        if ($investment->total_repayment == 0) {
            return 0;
        }

        return ($investment->paid_amount / $investment->total_repayment) * 100;
    }

    /**
     * Mark repayment as paid
     *
     * @param InvestmentRepayment $repayment
     * @param float $amount Amount paid
     * @param string|null $paymentMethod
     * @param string|null $reference
     * @return InvestmentRepayment
     */
    public function markRepaymentAsPaid(
        InvestmentRepayment $repayment,
        float $amount,
        ?string $paymentMethod = null,
        ?string $reference = null
    ): InvestmentRepayment {
        $repayment->update([
            'status' => 'paid',
            'paid_amount' => $amount,
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $reference,
        ]);

        // Update investment paid_amount
        $investment = $repayment->investment;
        $investment->increment('paid_amount', $amount);

        // Check if investment is fully paid
        if ($investment->paid_amount >= $investment->total_repayment) {
            $investment->update(['status' => 'completed']);
        }

        return $repayment;
    }

    /**
     * Get overdue repayments for an investment
     *
     * @param Investment $investment
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOverdueRepayments(Investment $investment)
    {
        return $investment->repayments()
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->get();
    }

    /**
     * Calculate late fee for overdue repayment
     *
     * @param InvestmentRepayment $repayment
     * @param float $lateFeePercentage
     * @return float
     */
    public function calculateLateFee(InvestmentRepayment $repayment, float $lateFeePercentage = 5.0): float
    {
        if ($repayment->status !== 'pending') {
            return 0;
        }

        $dueDate = Carbon::parse($repayment->due_date);
        $today = Carbon::today();

        if ($today->lte($dueDate)) {
            return 0;
        }

        // Calculate days overdue
        $daysOverdue = $today->diffInDays($dueDate);

        // Simple late fee calculation: percentage of amount per month overdue
        $monthsOverdue = ceil($daysOverdue / 30);
        $lateFee = $repayment->amount * ($lateFeePercentage / 100) * $monthsOverdue;

        return round($lateFee, 2);
    }
}
