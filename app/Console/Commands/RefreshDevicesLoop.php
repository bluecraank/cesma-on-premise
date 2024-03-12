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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $devices = Device::all();

        $timeUntilNextApiPull = time();

        while(true) {

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
