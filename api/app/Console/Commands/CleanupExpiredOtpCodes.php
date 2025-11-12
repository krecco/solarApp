<?php

namespace App\Console\Commands;

use App\Services\OtpService;
use Illuminate\Console\Command;

class CleanupExpiredOtpCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired OTP codes from the database';

    /**
     * Execute the console command.
     */
    public function handle(OtpService $otpService): void
    {
        $this->info('Cleaning up expired OTP codes...');
        
        $count = $otpService->cleanupExpiredCodes();
        
        $this->info("Removed {$count} expired OTP codes.");
    }
}
