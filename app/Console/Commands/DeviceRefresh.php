<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use App\Devices\ArubaOS;
use App\Devices\ArubaCX;
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

        static $models = [
            'aruba-os' => ArubaOS::class,
            'aruba-cx' => ArubaCX::class,
        ];

        $device = Device::find($this->argument('id'));
        
        if(!$device) {
            Log::error('Device not found');
            return;
        }

        $class = $models[$device->type];
        $class::refresh($device);

        Log::info('Device ' . $device->id . ' refreshed'. ' (' . (microtime(true) - $start) . 's)');
    }
}
