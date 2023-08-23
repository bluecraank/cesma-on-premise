<?php

namespace App\Console\Commands;

use App\Http\Controllers\BackupController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send backup mail';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        BackupController::sendMail();

        Log::info('Backup mail sent');
    }
}
