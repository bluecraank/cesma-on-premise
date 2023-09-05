<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\DevicePort;
use App\Models\Topology;
use Illuminate\Console\Command;

class ResolveTopology extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:resolve-topology';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find mac addresses of switches and resolve topology';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dev_macs = Device::all()->keyBy('id');

        $hostname_array = [];
        foreach($dev_macs as $device) {
            $hostname_array[strtolower($device->named)] = $device->id;
        }

        $mac_data = [];
        foreach($dev_macs as $device) {
            $snmp = [];

            try {
                // LLDP SNMP
                $snmp = snmp2_real_walk($device->hostname, 'public', '.1.0.8802.1.1.2.1.4.1.1', 5000000, 1);
                // LLDP Local Port to IfIndex
                $ifIndexes = snmp2_real_walk($device->hostname, 'public', '.1.0.8802.1.1.2.1.3.7.1.3', 5000000, 1);
            } catch(\Exception $e) {
                continue;
            }

            $ifIndexesKey = [];
            foreach($ifIndexes as $key => $value) {
                $key = explode(".", $key);
                $ifIndexesKey[$key[11]] = str_replace(["STRING: ", "\""], "", $value);
            }

            foreach($snmp as $key => $value) {
                // Seperate key by dot
                $key = explode(".", $key);

                switch($key[10]) {
                    // Port?
                    case 4:

                        break;
                    // Remote mac
                    case 5:
                        if(str_contains($value, "Hex-STRING:"))
                            $mac_data[$device->id][$key[12]]['mac_address'] = strtolower(str_replace(["Hex-STRING:", " "], "", $value));
                        break;

                    // Port ID
                    case 6:

                        break;

                    // Port ID remote?
                    case 7:
                        $mac_data[$device->id][$key[12]]['remote_port_2'] = str_replace(["STRING: ", "\""], "", $value);
                        break;

                    // Remote port description
                    case 8:
                        $port_desc = str_replace(["STRING: ", "\""], "", $value);
                        if($port_desc != "\"\"")
                            $mac_data[$device->id][$key[12]]['remote_port'] = $port_desc;
                        break;

                    // Remote Hostname?
                    case 9:
                        $hostname = strtolower(str_replace(["STRING: ", "\""], "", $value));
                        if($value != "\"\"") {
                            $mac_data[$device->id][$key[12]]['remote_hostname'] = $hostname;
                            if(isset($hostname_array[$hostname]))
                                $mac_data[$device->id][$key[12]]['remote_device'] = $hostname_array[$hostname];
                        }
                        break;

                    // Remote Sys name
                    case 10:
                        if(str_contains($value, "Aruba") || str_contains($value, "Dell EMC") || str_contains($value, "Dell Networking") || str_contains($value, "HP") || str_contains($value, "NETGEAR")) {
                            $mac_data[$device->id][$key[12]]['is_switch'] = true;
                            $mac_data[$device->id][$key[12]]['local_device'] = $device->id;
                            $mac_data[$device->id][$key[12]]['local_port'] = $ifIndexesKey[$key[12]] ?? 0;
                            $portName = DevicePort::where('device_id', $device->id)->where('snmp_if_index', $ifIndexesKey[$key[12]])->get('name');
                            if($portName && isset($portName[0])) {
                                $mac_data[$device->id][$key[12]]['local_port'] = $portName[0]->name;
                            } else {
                                unset($mac_data[$device->id][$key[12]]);
                            }

                        } else {
                            unset($mac_data[$device->id][$key[12]]);
                        }
                        break;
                }
            }

        }

        foreach($mac_data as $data) {
            foreach($data as $knoten) {
                if(str_contains($knoten['remote_port'], " ") || empty($knoten['remote_port'])) {
                    $remote_port = $knoten['remote_port_2'];
                } else {
                    $remote_port = $knoten['remote_port'];
                }

                if(isset($knoten['remote_port_2']) && str_contains($knoten['remote_port_2'], "/")) {
                    $remote_port = $knoten['remote_port_2'];
                }

                Topology::updateOrCreate([
                    'local_device' => $knoten['local_device'],
                    'local_port' => $knoten['local_port'] ?? 0,
                    'remote_device' => $knoten['remote_device'] ?? 0,
                    'remote_port' => $remote_port,
                    'remote_mac' => $knoten['mac_address'],
                ]);
            }
        }
    }
}
