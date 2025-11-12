<?php

namespace App\Console\Commands;

use App\Mail\RepaymentReminderMail;
use App\Models\InvestmentRepayment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendRepaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repayments:send-reminders {--days=7 : Number of days before due date to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for upcoming repayments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysAhead = $this->option('days');
        $targetDate = Carbon::today()->addDays($daysAhead);

        $this->info("Sending repayment reminders for payments due on {$targetDate->format('Y-m-d')}");

        // Find repayments due on target date
        $repayments = InvestmentRepayment::with(['investment.user', 'investment.solarPlant'])
            ->where('status', 'pending')
            ->whereDate('due_date', $targetDate)
            ->get();

        if ($repayments->isEmpty()) {
            $this->info('No repayments due on this date.');
            return 0;
        }

        $this->info("Found {$repayments->count()} repayment(s) to remind.");

        $sent = 0;
        $failed = 0;

        foreach ($repayments as $repayment) {
            try {
                Mail::to($repayment->investment->user->email)
                    ->send(new RepaymentReminderMail($repayment, $daysAhead));

                $this->line("✓ Sent reminder to {$repayment->investment->user->email} for repayment #{$repayment->id}");
                $sent++;

                // Log activity
                activity()
                    ->performedOn($repayment)
                    ->withProperties([
                        'days_until_due' => $daysAhead,
                        'amount' => $repayment->amount,
                    ])
                    ->log('sent repayment reminder email');
            } catch (\Exception $e) {
                $this->error("✗ Failed to send reminder to {$repayment->investment->user->email}: {$e->getMessage()}");
                $failed++;

                // Log error
                activity()
                    ->performedOn($repayment)
                    ->withProperties([
                        'error' => $e->getMessage(),
                        'days_until_due' => $daysAhead,
                    ])
                    ->log('failed to send repayment reminder email');
            }
        }

        $this->newLine();
        $this->info("Summary: {$sent} sent, {$failed} failed");

        return 0;
    }
}
