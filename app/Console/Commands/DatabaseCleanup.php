<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\DeviceBackup;
use App\Models\DeviceCustomUplink;
use App\Models\DevicePortStat;
use App\Models\DeviceUplink;
use App\Models\Log;
use App\Models\Mac;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as FacadesLog;

class DatabaseCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Mac::whereDate('created_at', '<=', now()->subWeek(1))->delete();
        Client::whereDate('updated_at', '<=', now()->subWeek(8))->delete();
        DeviceBackup::whereDate('created_at', '<=', now()->subYear(2))->delete();
        DevicePortStat::whereDate('created_at', '<=', now()->subWeek(2))->delete();
        // Log::whereDate('created_at', '<=', now()->subWeek(8))->delete();
        
        
        $uplinks = DeviceUplink::all()->keyBy('id')->groupBy('device_id')->toArray();

        foreach ($uplinks as $uplink) {
            foreach ($uplink as $key => $value) {
                Client::where('port_id', $value['name'])->where('device_id', $value['device_id'])->delete();
            }
        }
        
        $custom_uplinks = DeviceCustomUplink::all()->keyBy('id')->groupBy('device_id')->toArray();

        foreach ($custom_uplinks as $dev_id => $device) {
            foreach ($device as $key => $value) {
                $uplinks = json_decode($value['uplinks'], true);

                foreach ($uplinks as $uplink) {
                    Client::where('port_id', $uplink)->where('device_id', $dev_id)->delete();
                }
            }
        }

        \Illuminate\Support\Facades\Log::info('Database cleaned up');
    }
}
