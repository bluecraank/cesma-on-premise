<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;

class RefreshDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:refresh-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh all devices';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $devices = Device::all();

        foreach($devices as $device) {
            proc_open('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' > /dev/null &', [], $pipes);
            // $this->info('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' > /dev/null &');
            $this->info('Start refreshing device ' . $device->id . '...');
        }
    }
}
