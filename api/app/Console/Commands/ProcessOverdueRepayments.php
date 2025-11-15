<?php

namespace App\Console\Commands;

use App\Models\InvestmentRepayment;
use App\Services\ActivityService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessOverdueRepayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repayments:process-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark overdue repayments and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $activityService = app(ActivityService::class);
        $today = Carbon::today();

        $this->info("Processing overdue repayments as of {$today->format('Y-m-d')}");

        // Find pending repayments that are overdue
        $overdueRepayments = InvestmentRepayment::with(['investment.user', 'investment.solarPlant'])
            ->where('status', 'pending')
            ->where('due_date', '<', $today)
            ->get();

        if ($overdueRepayments->isEmpty()) {
            $this->info('No overdue repayments found.');
            return 0;
        }

        $this->info("Found {$overdueRepayments->count()} overdue repayment(s).");

        $processed = 0;

        foreach ($overdueRepayments as $repayment) {
            $daysOverdue = $today->diffInDays(Carbon::parse($repayment->due_date));

            $this->line("Processing repayment #{$repayment->id} - {$daysOverdue} days overdue");

            // Update status to overdue if not already
            if ($repayment->status !== 'overdue') {
                $repayment->update(['status' => 'overdue']);

                // Log activity
                $activityService->log(
                    'marked repayment as overdue',
                    $repayment,
                    null,
                    [
                        'days_overdue' => $daysOverdue,
                        'amount' => $repayment->amount,
                        'due_date' => $repayment->due_date,
                    ]
                );

                $processed++;
            }
        }

        $this->newLine();
        $this->info("Processed {$processed} overdue repayment(s).");

        return 0;
    }
}
