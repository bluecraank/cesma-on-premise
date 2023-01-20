<?php

namespace App\Console\Commands;

use App\Http\Controllers\BackupController;
use Illuminate\Console\Command;

class BackupDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup all devices';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return $this->comment(BackupController::backupAll());
    }
}
