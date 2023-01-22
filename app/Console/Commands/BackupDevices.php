<?php

namespace App\Console\Commands;

use App\Http\Controllers\BackupController;
use App\Models\Device;
use Illuminate\Console\Command;

class BackupDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:backup-all';

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
        $devices = Device::all();

        foreach($devices as $device) {
            proc_open('php ' . base_path() . '/artisan device:backup ' . $device->id . ' > /dev/null &', [], $pipes);
            // $this->info('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' > /dev/null &');
            $this->info('Start backup device ' . $device->id . '...');
        }
    }
}
