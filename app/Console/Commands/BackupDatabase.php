<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database backup command.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Artisan::call('backup:run', ['--only-db' => true]);
        $this->info('Database backup completed!');
    }
}
