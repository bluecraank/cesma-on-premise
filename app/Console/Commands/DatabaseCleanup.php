<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\DeviceBackup;
use App\Models\DevicePortStat;
use App\Models\DeviceUplink;
use App\Models\Log;
use App\Models\Mac;
use App\Models\Vlan;
use App\Models\SnmpMacData;
use Illuminate\Console\Command;

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
        Log::whereDate('created_at', '<=', now()->subWeek(8))->delete();
        SnmpMacData::whereDate('updated_at', '<=', now()->subWeek(4))->delete();
        \App\Models\Notification::whereDate('created_at', '<=', now()->subWeek(8))->where('status', '!=', "declined")->delete();

        $vlans_ignore = Vlan::where('is_client_vlan', 0)->get()->keyBy('vid')->toArray();

        Client::where(function($query) use ($vlans_ignore) {
            $query->where('vlan_id', 0);
            foreach ($vlans_ignore as $vlan) {
                $query->orWhere('vlan_id', $vlan['vid']);
            }
        })->delete();

        $array_uplinks = [];
        $uplinks = DeviceUplink::all()->keyBy('id')->groupBy('device_id')->toArray();
        foreach($uplinks as $dev_id => $uplink) {
            foreach($uplink as $each_uplink) {
                $array_uplinks[$dev_id][] = $each_uplink['name'];
            }
        }

        foreach($array_uplinks as $device_id => $uplinks) {
            var_dump($uplinks);
            Client::where('device_id', $device_id)->whereIn('port_id', $uplinks)->delete();
        }


        \Illuminate\Support\Facades\Log::info('[System] Database cleaned up');
    }
}
