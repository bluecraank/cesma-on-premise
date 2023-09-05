<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeviceRefreshAllApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:refresh-all-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh all devices with API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $devices = Device::all();

        // API refresh
        foreach($devices as $device) {
            proc_open('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' api > /dev/null &', [], $pipes);
        }


        Log::info('[Devices - API] Refreshing '.count($devices).' devices...');
    }
}
