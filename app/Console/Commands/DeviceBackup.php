<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use App\Devices\ArubaCX;
use App\Devices\ArubaOS;
use Illuminate\Support\Facades\Log;

class DeviceBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:backup {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup specific device';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
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
        $class::createBackup($device);

        Log::info('Backup for device ' . $device->id . ' created');
    }
}
