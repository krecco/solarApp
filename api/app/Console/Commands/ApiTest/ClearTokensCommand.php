<?php

namespace App\Console\Commands\ApiTest;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearTokensCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test:clear-tokens 
        {--all : Clear all token files}
        {--regular : Clear only regular bearer token}
        {--admin : Clear only admin bearer token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear saved authentication tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tokenFile = base_path('cli_tests/bearer.txt');
        $adminTokenFile = base_path('cli_tests/admin-bearer.txt');
        $cleared = [];

        // Determine what to clear
        $clearRegular = $this->option('all') || $this->option('regular') || (!$this->option('admin'));
        $clearAdmin = $this->option('all') || $this->option('admin');

        // Clear regular token
        if ($clearRegular && File::exists($tokenFile)) {
            File::delete($tokenFile);
            $cleared[] = 'bearer.txt';
        }

        // Clear admin token
        if ($clearAdmin && File::exists($adminTokenFile)) {
            File::delete($adminTokenFile);
            $cleared[] = 'admin-bearer.txt';
        }

        // Clear any custom token files
        $customFiles = File::glob(base_path('cli_tests/*-bearer.txt'));
        foreach ($customFiles as $file) {
            $filename = basename($file);
            if ($filename !== 'bearer.txt' && $filename !== 'admin-bearer.txt') {
                if ($this->option('all')) {
                    File::delete($file);
                    $cleared[] = $filename;
                }
            }
        }

        if (empty($cleared)) {
            $this->warn('No token files found to clear.');
        } else {
            $this->info('🗑️  Cleared token files:');
            foreach ($cleared as $file) {
                $this->line("   - {$file}");
            }
        }

        return 0;
    }
}
