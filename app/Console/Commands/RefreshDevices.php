<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

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

        // SNMP refresh
        foreach($devices as $device) {
            proc_open('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' snmp > /dev/null &', [], $pipes);
        }

        sleep(5);

        // API refresh
        foreach($devices as $device) {
            proc_open('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' api > /dev/null &', [], $pipes);
        }

        Log::info('[Switch] Refreshing '.count($devices).' devices...');
    }
}
