<?php

namespace App\Http\Controllers;

use App\EndpointProviders\Baramundi;
use App\Models\Device;
use App\Models\Endpoint;
use Illuminate\Support\Str;

class EndpointController extends Controller
{

    public function index() {

        $clients = Endpoint::all()->sortBy('hostname');

        return view('endpoints.index', compact('clients'));
    }

    public function getEndpoints() { 

        //ddd(config('app.baramundi_api_url'));
        if(!empty(config('app.baramundi_api_url'))) {
            $provider = new Baramundi;
            $endpoints = $provider->queryClientData();

            return $endpoints;
        }
    }

    public function getMacAddressTables() {

        $device = Device::all()->keyBy('id');

        $macAddressTables = [];
        
        $i = 0;
        foreach($device as $switch) {
            $macTable = json_decode($switch->mac_table_data, true);
            $macData = (isset($macTable['mac_table_entry_element'])) ? $macTable['mac_table_entry_element'] : [];
            

            $withNameData = [];
            foreach($macData as $entry) {
                if(str_contains($entry['port_id'], "Trk") or str_contains($entry['port_id'], "48")) {
                    continue; 
                 }
                $withNameData[$i] = $entry;
                $withNameData[$i]['device_id'] = $switch->id;
                $withNameData[$i]['device_name'] = $switch->name;
                $i++;
            }

            $macAddressTables = array_merge($macAddressTables, $withNameData);
        }
    
        return $macAddressTables;
    }

    public function getFormattedMacs($macAddressTables) {

        $returnFormattedTable = [];

        $i = 0;
        foreach($macAddressTables as $key => $mac) {
            $fmac = strtolower(str_replace([":", "-"], "", $mac['mac_address']));

            $returnFormattedTable[$i] = $fmac;
            $i++;
        }

        return $returnFormattedTable;
    }

    public function getMergedData() {
        $start = microtime(true);
        $endpoint_data = $this->getEndpoints();
        $mac_data = $this->getMacAddressTables();
        $formatted_macs = $this->getFormattedMacs($mac_data);

        $loops = 0;
        $i = 0;

        foreach($endpoint_data as $client) {
            foreach($client->mac_addresses as $mac) {
                if($key = array_search($mac, $formatted_macs)) {

                    echo $key . " | " . $client->hostname . " | " . $client->ip_address . " | " . $mac . " | " . $mac_data[$key]['port_id'] . " | " . $mac_data[$key]['vlan_id'] . " | " . $mac_data[$key]['device_name'] . "</br>";
                    $i++;
                }

                $loops++;
            }
        }
        $time_elapsed_secs = microtime(true) - $start;
        return $time_elapsed_secs. " | " . $loops . " | " . count($formatted_macs) . " | " . count($mac_data) . " | " . $i;

    }

    static function updateEndpointsOfSwitch($id = 1) {
        $device = Device::find($id);
        if($device) {
            $macAddressTable = json_decode($device->mac_table_data, true);
            $macData = (isset($macAddressTable['mac_table_entry_element'])) ? $macAddressTable['mac_table_entry_element'] : [];
            
            $withNameData = [];
            $i = 0;
            foreach($macData as $entry) {
                if(str_contains($entry['port_id'], "Trk") or str_contains($entry['port_id'], "48")) {
                    continue; 
                 }
                $withNameData[$i] = $entry;
                $i++;
            }

            $endpoint_data = $this->getEndpoints();
            $formatted_macs = $this->getFormattedMacs($withNameData);

            $i = 0;
            foreach($endpoint_data as $client) {
                foreach($client->mac_addresses as $mac) {
                    if($key = array_search($mac, $formatted_macs)) {
                        $endpoint = new Endpoint();
                        $endpoint->switch_id = $device->id;
                        
                        $endpoint->hostname = strtolower($client->hostname);
                        if($endpoint->hostname == "" or $endpoint->hostname == null) {
                            $endpoint->hostname = "UNK-".Str::random(10);
                        }
                        $endpoint->ip_address = $client->ip_address;
                        $endpoint->mac_address = $mac;
                        $endpoint->port_id = $withNameData[$key]['port_id'];
                        $endpoint->vlan_id = $withNameData[$key]['vlan_id'];

                        if(!empty($endpoint->ip_address)) {
                            $endpoint->save();
                        }
                        
                        echo $key . " | " . $client->hostname . " | " . $client->ip_address . " | " . $mac . " | " . $withNameData[$key]['port_id'] . " | " . $withNameData[$key]['vlan_id'] . " | " . $device->name . "</br>";
                        $i++;
                    }
                }
            }

        }
    }
}
