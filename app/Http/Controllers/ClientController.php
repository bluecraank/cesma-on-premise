<?php

namespace App\Http\Controllers;

use App\ClientProviders\Baramundi;
use App\Models\Device;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ClientController extends Controller
{

    public function index() {

        $clients = Client::all()->sortBy('hostname');
        $devices = Device::all()->keyBy('id');

        return view('clients.index', compact('clients', 'devices'));
    }

    static function getClientsFromProviders() { 
        if(!empty(config('app.baramundi_api_url'))) {
            $provider = new Baramundi;
            $endpoints = $provider->queryClientData();

            return $endpoints;
        }
    }

    static function storeMergedClientData() {
        $endpoint_data = ClientController::getClientsFromProviders();
        $mac_data = DeviceController::getMacAddressesFromDevices(); 

        if($endpoint_data == null or empty($endpoint_data)) {
            return dd("Keine Endpoints der Provider erhalten");
        }
    
        foreach($endpoint_data as $client) {
            foreach($client->mac_addresses as $mac) {
                if($key = array_search($mac, $mac_data[0])) {
                    $endpoint = new Client();
                    $endpoint->switch_id = $mac_data[1][$key]['device_id'];
                    
                    $endpoint->hostname = strtolower($client->hostname);
                    if($endpoint->hostname == "" or $endpoint->hostname == null) {
                        $endpoint->hostname = "UNK-".Str::random(10);
                    }

                    $endpoint->id = md5($client->ip_address."".$mac);
                    $endpoint->ip_address = $client->ip_address;
                    $endpoint->mac_address = $mac;
                    $endpoint->port_id = $mac_data[1][$key]['port'];
                    $endpoint->vlan_id = $mac_data[1][$key]['vlan'];

                    // Check for existence of endpoint
                    if($dev = Client::find($endpoint->id)) {
                        $dev->update([
                            'hostname' => $endpoint->hostname,
                            'switch_id' => $endpoint->switch_id,
                            'vlan_id' => $endpoint->vlan_id,
                            'port_id' => $endpoint->port_id,
                        ]);
                    } else {
                        $endpoint->save();  
                    }
               
                }
            }
        }

        dd("Clients updated");
    }

    static function getClientsFromSwitch(Request $request) {
        $device = Device::find($request->input('id'));

        $endpoint_data = ClientController::getClientsFromProviders();

        if($endpoint_data == null or empty($endpoint_data)) {
            return json_encode(['success' => 'false', 'error' => 'Client provider returned no data']);
 
        }

        $DataToIds = [];
        $MacsToIds = [];
        $i = 0;
        $macTable = json_decode($device->mac_table_data, true);
        $macData = (isset($macTable)) ? $macTable : [];
        $MacAddressesData = [];
        foreach($macData as $entry) {
            if(str_contains($entry['port'], "Trk") or str_contains($entry['port'], "48")) {
                continue;
            }
            $MacAddressesData[$i] = $entry;
            $MacAddressesData[$i]['device_id'] = $device->id;
            $MacAddressesData[$i]['device_name'] = $device->name;
            $MacsToIds[$i] = strtolower(str_replace([":", "-"], "", $entry['mac']));
            $i++;
        }

        $DataToIds = array_merge($DataToIds, $MacAddressesData);
        
        $mac_data = [$MacsToIds, $DataToIds];

        foreach($endpoint_data as $client) {
            foreach($client->mac_addresses as $mac) {
                if($key = array_search($mac, $mac_data[0])) {
                    $endpoint = new Client();
                    $endpoint->switch_id = $mac_data[1][$key]['device_id'];
                    
                    $endpoint->hostname = strtolower($client->hostname);
                    if($endpoint->hostname == "" or $endpoint->hostname == null) {
                        $endpoint->hostname = "UNK-".Str::random(10);
                    }

                    $endpoint->id = md5($client->ip_address."".$mac);
                    $endpoint->ip_address = $client->ip_address;
                    $endpoint->mac_address = $mac;
                    $endpoint->port_id = $mac_data[1][$key]['port'];
                    $endpoint->vlan_id = $mac_data[1][$key]['vlan'];

                    // Check for existence of endpoint
                    if($dev = Client::find($endpoint->id)) {
                        $dev->update([
                            'hostname' => $endpoint->hostname,
                            'switch_id' => $endpoint->switch_id,
                            'vlan_id' => $endpoint->vlan_id,
                            'port_id' => $endpoint->port_id,
                        ]);
                    } else {
                        $endpoint->save();  
                    }
               
                }
            }
        }

        return json_encode(['success' => 'true', 'error' => 'Clients updated']);

    }
}
