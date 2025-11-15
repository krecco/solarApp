<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateLogViewerUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log-viewer:url {email? : The email of the admin user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a secure URL to access the log viewer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // If no email provided, list all admins
        if (!$email) {
            $admins = User::role('admin')->get();

            if ($admins->isEmpty()) {
                $this->error('No admin users found!');
                return 1;
            }

            $this->info('Available admin users:');
            foreach ($admins as $admin) {
                $this->line("  - {$admin->email}");
            }

            $email = $this->ask('Enter admin email');
        }

        // Find the user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found: {$email}");
            return 1;
        }

        if (!$user->hasRole('admin')) {
            $this->error("User {$email} is not an admin!");
            return 1;
        }

        // Get or create a token for this user
        $token = $user->tokens()
            ->where('name', 'log-viewer-access')
            ->first();

        if (!$token) {
            $token = $user->createToken('log-viewer-access');
            $tokenString = $token->plainTextToken;
        } else {
            $tokenString = $token->token->plainTextToken ?? $token->plainTextToken;

            // If we can't get the plain text (it's hashed), create a new one
            if (!$tokenString) {
                $this->warn('Creating new token (old token was already hashed)...');
                $token->delete();
                $token = $user->createToken('log-viewer-access');
                $tokenString = $token->plainTextToken;
            }
        }

        $url = config('app.url') . '/log-viewer?token=' . $tokenString;

        $this->newLine();
        $this->info('✓ Log Viewer URL generated successfully!');
        $this->newLine();
        $this->line('URL:');
        $this->line($url);
        $this->newLine();
        $this->warn('⚠️  Keep this URL secret! Anyone with this URL can access your logs.');
        $this->warn('⚠️  This token does not expire. Revoke it when done:');
        $this->line('    php artisan log-viewer:revoke ' . $email);
        $this->newLine();

        return 0;
    }
}
