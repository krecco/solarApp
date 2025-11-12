<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Dotenv\Dotenv;

class ReloadEnvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:reload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force reload environment variables from .env file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Reloading environment variables...');
        
        // Clear all caches
        $this->call('config:clear');
        $this->call('cache:clear');
        $this->call('optimize:clear');
        
        // Force reload .env
        $dotenv = Dotenv::createMutable(base_path());
        $dotenv->load();
        
        $this->info('Environment variables reloaded!');
        $this->newLine();
        
        // Display current SMTP configuration
        $this->info('Current SMTP Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['MAIL_MAILER', env('MAIL_MAILER')],
                ['MAIL_HOST', env('MAIL_HOST')],
                ['MAIL_PORT', env('MAIL_PORT')],
                ['MAIL_USERNAME', env('MAIL_USERNAME')],
                ['MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')],
                ['MAIL_ENCRYPTION', env('MAIL_ENCRYPTION') ?? 'none'],
            ]
        );
        
        return Command::SUCCESS;
    }
}
