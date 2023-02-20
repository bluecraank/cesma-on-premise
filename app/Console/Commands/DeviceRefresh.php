<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use App\Http\Controllers\DeviceController;
use App\Services\DeviceService;
use App\Helper\CLog;
use Illuminate\Support\Facades\Log;

class DeviceRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:refresh {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh specific device';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $start = microtime(true);

        $device = Device::find($this->argument('id'));
        
        if(!$device) {
            CLog::error("JobHandler", "Failed to refresh device, device not found", $device);
            Log::error("Failed to refresh device, device not found");
            return;
        }

        DeviceService::refreshDevice($device);
        CLog::info("JobHandler", "Successfully refreshed device " . $device->name, $device, "Took " . number_format(microtime(true) - $start, 2) . " sec.");
        Log::info("Successfully refreshed device " . $device->name . " Took " .  number_format(microtime(true) - $start, 2) . " seconds to refresh device");
    }
}
