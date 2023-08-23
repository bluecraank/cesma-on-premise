<?php

namespace App\Console\Commands;

use App\Devices\DellEMC;
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
    protected $signature = 'device:refresh {id} {type}';

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
        $device = Device::find($this->argument('id'));

        if(!$device) {
            Log::error("Failed to refresh device, device not found");
            return;
        }

        $refresh = DeviceService::refreshDevice($device, $this->argument('type'));
        $refreshStatus = json_decode($refresh, true);

        if($refreshStatus['success'] == "false") {
            Log::error("Failed to refresh device " . $device->name);
            Command::error("Failed to refresh device " . $device->name);
            return;
        }

        Log::info("Successfully refreshed device " . $device->name);
    }
}
