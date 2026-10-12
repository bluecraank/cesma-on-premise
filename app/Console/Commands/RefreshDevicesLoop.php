<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RefreshDevicesLoop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-devices-loop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh all devices continuously (SNMP, every 5 minutes API), runs as service';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeUntilNextApiPull = time();

        while(true) {

            // Reload every round, so new devices are refreshed and deleted ones are skipped
            $devices = Device::all();

            if(count($devices) == 0) {
                sleep(30);
                continue;
            }

            $type = "snmp";

            if(time() - $timeUntilNextApiPull >= 300) {// More than 5 minutes, fetch api
                $timeUntilNextApiPull = time();
                $type = "api";
            }

            foreach($devices as $device) {
                Artisan::call('device:refresh', ['id'=>$device->id, 'type'=>$type]);
                echo $type . "\n";
            }
        }
    }
}
