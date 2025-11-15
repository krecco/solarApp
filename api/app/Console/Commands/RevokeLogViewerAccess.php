<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RevokeLogViewerAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log-viewer:revoke {email? : The email of the admin user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revoke log viewer access token for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        if (!$email) {
            $email = $this->ask('Enter admin email');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found: {$email}");
            return 1;
        }

        // Delete log-viewer-access tokens
        $deleted = $user->tokens()
            ->where('name', 'log-viewer-access')
            ->delete();

        if ($deleted > 0) {
            $this->info("✓ Revoked {$deleted} log viewer token(s) for {$email}");
        } else {
            $this->warn("No log viewer tokens found for {$email}");
        }

        return 0;
    }
}
