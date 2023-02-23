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
            CLog::error("Automated task", "Failed to refresh device, device not found", $device);
            Log::error("Failed to refresh device, device not found");
            return;
        }

        $refresh = DeviceService::refreshDevice($device);
        $refreshStatus = json_decode($refresh, true);

        if($refreshStatus['success'] == "false") {
            CLog::error("Automated task", "Failed to refresh device " . $device->name, $device, $refreshStatus['message']);
            Log::error("Failed to refresh device " . $device->name);
            return;
        }

        CLog::info("Automated task", "Successfully refreshed device " . $device->name, $device, "Took " . number_format(microtime(true) - $start, 2) . " sec.");
        Log::info("Successfully refreshed device " . $device->name);
    }
}
