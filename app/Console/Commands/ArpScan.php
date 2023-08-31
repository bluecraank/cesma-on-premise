<?php

namespace App\Console\Commands;

use App\Models\Building;
use App\Models\Device;
use App\Models\Room;
use App\Models\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ArpScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devices:arp-scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan for devices on the network and find new devices.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $result = shell_exec('ip neighbour');
        $array = explode("\n", $result);

        $devices = Device::all()->keyBy('hostname')->toArray();

        $site = Site::first();
        $building = Building::where('site_id', $site->id)->first();
        $room = Room::where('building_id', $building->id)->first();

        $hosts = [];

        foreach ($array as $key => $value) {
            if ($key == 0) {
                continue;
            }

            $value = explode(" ", $value);

            if (!isset($value[2]) || !isset($value[0]) || $value[4] == "(incomplete)" || !str_contains($value[4], ":")) {
                continue;
            }

            $hosts[] = [
                'ip' => $value[0],
                'mac' => $value[4],
            ];
        }

        foreach ($hosts as $neighbour) {
            $snmp = null;
            try {
                $snmp = snmp2_get($neighbour['ip'], 'public', ".1.3.6.1.2.1.47.1.1.1.1.13.1", 5000000, 1);
            } catch (\Exception $e) {

            }

            if ($snmp === false || $snmp == "") {
                try {
                    $snmp = snmp2_get($neighbour['ip'], 'public', ".1.3.6.1.2.1.1.1.0", 5000000, 1);
                    if(!str_contains($snmp, "Switch") && !str_contains($snmp, "ProCurve")) {
                        $snmp = false;
                    }
                } catch (\Exception $e) {
                    continue;
                }

                if($snmp === false || $snmp == "") {
                    continue;
                }
            }

            $model = str_replace(["STRING: ", "\""], "", $snmp);

            $cx_models = [
                "JL676A",
                "JL675A",
                "JL677A",
                "JL678A",
                "JL679A",
            ];


            $hostname = gethostbyaddr($neighbour['ip']);
            $full_hostname = $hostname;

            if($hostname == "") {
                $hostname = $neighbour['ip'];
            } else {
                $hostname = explode(".", $hostname)[0] ?? $hostname;
            }

            $hostname = strtoupper($hostname);

            if(isset($devices[$full_hostname]) || isset($devices[$hostname]) || isset($devices[$neighbour['ip']])) {
                continue;
            }

            $type = "aruba-os";

            if(in_array($model, $cx_models)) {
                $type = "aruba-cx";
            }

            if($model == "") {
                $type = "dell-emc";
            }

            Log::info("ARP: Found new device: " . $hostname . " (" . $neighbour['ip'] . ") - " . $model . " - " . $type);

            Device::firstOrCreate([
                'name' => $hostname,
            ], [
                'hostname' => $full_hostname,
                'mac_address' => $neighbour['mac'],
                'type' => $type,
                'password' => Crypt::encrypt("public"),
                'site_id' => $site->id,
                'building_id' => $building->id,
                'room_id' => $room->id,
            ]);
        }
    }
}
