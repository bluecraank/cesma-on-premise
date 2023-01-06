<?php

namespace App\Http\Controllers;

use App\EndpointProviders\Baramundi;
use App\Models\Device;
use App\Models\Endpoint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EndpointController extends Controller
{

    public function index() {

        $clients = Endpoint::all()->sortBy('hostname');
        $devices = Device::all()->keyBy('id');

        return view('endpoints.index', compact('clients', 'devices'));
    }

    static function getClientsFromProviders() { 
        if(!empty(config('app.baramundi_api_url'))) {
            $provider = new Baramundi;
            $endpoints = $provider->queryClientData();

            return $endpoints;
        }
    }

    static function storeMergedClientData() {
        $endpoint_data = EndpointController::getClientsFromProviders();
        $mac_data = DeviceController::getMacAddressesFromDevices(); 

        if($endpoint_data == null or empty($endpoint_data)) {
            return dd("Keine Endpoints der Provider erhalten");
        }
        
        Endpoint::truncate();
    
        foreach($endpoint_data as $client) {
            foreach($client->mac_addresses as $mac) {
                if($key = array_search($mac, $mac_data[0])) {
                    $endpoint = new Endpoint();
                    $endpoint->switch_id = $mac_data[$key]['device_id'];
                    
                    $endpoint->hostname = strtolower($client->hostname);
                    if($endpoint->hostname == "" or $endpoint->hostname == null) {
                        $endpoint->hostname = "UNK-".Str::random(10);
                    }

                    $endpoint->id = md5($client->ip_address."".$mac);
                    $endpoint->ip_address = $client->ip_address;
                    $endpoint->mac_address = $mac;
                    $endpoint->port_id = $mac_data[$key]['port_id'];
                    $endpoint->vlan_id = $mac_data[$key]['vlan_id'];
                    $endpoint->save();                  
                }
            }
        }
    }

    static function storeMergedClientDataOfSwitch($id = 1) {
        $device = Device::find($id);
        if($device) {
            $macAddresses = json_decode($device->mac_table_data, true);
            $macData = (isset($macAddresses['mac_table_entry_element'])) ? $macAddresses['mac_table_entry_element'] : [];
            
            $DataToIds = [];
            $MacsToIds = [];
            $i = 0;

            $macTable = json_decode($device->mac_table_data, true);
            $macData = (isset($macTable['mac_table_entry_element'])) ? $macTable['mac_table_entry_element'] : [];
            $MacAddressesData = [];
            foreach($macData as $entry) {
                if(str_contains($entry['port_id'], "Trk") or str_contains($entry['port_id'], "48")) {
                    continue;
                }
                $MacAddressesData[$i] = $entry;
                $MacAddressesData[$i]['device_id'] = $device->id;
                $MacAddressesData[$i]['device_name'] = $device->name;
                $MacsToIds[$i] = strtolower(str_replace([":", "-"], "", $entry['mac_address']));
                $i++;
            }

            $DataToIds = array_merge($DataToIds, $MacAddressesData);
            
            $endpoint_data = EndpointController::getClientsFromProviders();

            foreach($endpoint_data as $client) {
                foreach($client->mac_addresses as $mac) {
                    if($key = array_search($mac, $MacsToIds)) {
                        $endpoint = new Endpoint();
                        $endpoint->switch_id = $device->id;
                        
                        $endpoint->hostname = strtolower($client->hostname);
                        if($endpoint->hostname == "" or $endpoint->hostname == null) {
                            $endpoint->hostname = "UNK-".Str::random(10);
                        }
                        
                        $endpoint->id = md5($client->ip_address."".$mac);
                        $endpoint->ip_address = $client->ip_address;
                        $endpoint->mac_address = $mac;
                        $endpoint->port_id = $MacAddressesData[$key]['port_id'];
                        $endpoint->vlan_id = $MacAddressesData[$key]['vlan_id'];
                        $endpoint->save();
                    }
                }
            }
            return true;
        }
        return false;
    }
}
