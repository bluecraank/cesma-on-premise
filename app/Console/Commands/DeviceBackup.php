<?php

namespace App\Console\Commands;

use App\Helper\CLog;
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

        $device = Device::find($this->argument('id'));

        if(!$device) {
            Log::error(__('Device not found'));
            return;
        }

        if (!(config('app.backup_support')[$device->type] ?? false)) {
            Log::info('Backup for device ' . $device->id . ' skipped, device type does not support backups');
            return;
        }

        $class = config('app.types')[$device->type];

        if ($class::createBackup($device)) {
            Log::info('Backup for device ' . $device->id . ' created');
        } else {
            Log::error('Backup for device ' . $device->id . ' failed');
            CLog::error("Backup", "Scheduled backup of switch {$device->name} failed", $device);
        }
    }
}
