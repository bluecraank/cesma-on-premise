<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckJobQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:job-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the job queue for pending jobs.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('Checking job queue...');
        return Command::SUCCESS;
    }
}
