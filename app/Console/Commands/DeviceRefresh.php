<?php

namespace App\Console\Commands;

use App\Devices\DellEMC;
use App\Models\Device;
use Illuminate\Console\Command;
use App\Services\DeviceService;
use App\Helper\CLog;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;

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
            if($this->argument('type') == "api") {
                Log::info("TEST");
                $notification = Notification::firstOrCreate([
                    'unique-identifier' => "device-refresh-failed-".$device->id,
                ],[
                    'title' => $device->name,
                    'type' => 'error',
                    'message' => "Login failed or no connection",
                    'device_id' => $device->id,
                    'data' => "Login failed or no connection",
                ]);

                $notification->touch();
            }

            Log::error("[".$this->argument('type')."] Failed to refresh device " . $device->name);
            return;
        }
    }
}
