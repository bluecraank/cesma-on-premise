<?php

namespace App\Http\Controllers;

use App\ClientProviders\Baramundi;
use App\Models\Device;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Acamposm\Ping\Ping;
use Acamposm\Ping\PingCommandBuilder;
use App\ClientProviders\SNMP_Sophos_XG;
use Carbon\Carbon;

class ClientController extends Controller
{

    public function index() {

        $clients = Client::all();
        $devices = Device::all()->keyBy('id');

        return view('client.index', compact('clients', 'devices'));
    }

    static function getClientsFromProviders() { 
       
        // Baramundi
        if(!empty(config('app.baramundi_api_url'))) {
            $provider = new Baramundi;
            $endpoints = $provider->queryClientData();

        
        }

        // Sophos XG
        $data = SNMP_Sophos_XG::queryClientData();


        // Returns: Array of mac_addresses, hostname and ip_address
        return array_merge($endpoints, $data);
    }

    static function debugClientsFromProviders() {
        $data = ClientController::getClientsFromProviders();
        dd($data);
    }

    static function getClientsAllDevices() {
        $endpoint_data = ClientController::getClientsFromProviders();
        $mac_data = DeviceController::getMacAddressesFromDevices(); 

        if($endpoint_data == null or empty($endpoint_data)) {
            return dd("Keine Endpoints der Provider erhalten");
        }
        
        $i = 1;
        $is = 1;
        $found = 1;
        $new = 0;

        foreach($endpoint_data as $client) {
            foreach($client['mac_addresses'] as $mac) {
                if($key = array_search($mac, $mac_data[0])) {
                    $endpoint = new Client();
                    $endpoint->switch_id = $mac_data[1][$key]['device_id'];
                    
                    $endpoint->hostname = strtolower($client['hostname']);
                    if($endpoint->hostname == "" or $endpoint->hostname == null) {
                        $endpoint->hostname = "UNK-".Str::random(10);
                    }

                    $endpoint->id = md5($client['ip_address']."".$mac);
                    $endpoint->ip_address = $client['ip_address'];
                    $endpoint->mac_address = $mac;
                    $endpoint->port_id = $mac_data[1][$key]['port'];
                    $endpoint->vlan_id = $mac_data[1][$key]['vlan'];


                    // Check for existence of endpoint
                    $dev = Client::find($endpoint->id);

                    if($dev !== null) { 
                        $dev->update([
                            'switch_id' => $endpoint->switch_id,
                            'vlan_id' => $endpoint->vlan_id,
                            'port_id' => $endpoint->port_id,
                            'hostname' => str_contains($endpoint->hostname, 'UNK-') ? $dev->hostname : $endpoint->hostname,
                            'id' => $endpoint->id,
                        ]);
                        $found++;
                    } else {

                        $endpoint->save();  
                        $new++;
                    }

                    $is++;
                }
            }
            $i++;
        }

        echo "MACs: ".$i." | MACs found on Switch: ".$is. " | Already saved: ".$found." | New: ".$new ."\n";
        return json_encode(['success' => 'true', 'error' => 'Clients updated']);
    }

    static function getClientsFromSwitch(Request $request) {
        $device = Device::find($request->input('id'));

        if($device == null) {
            return json_encode(['success' => 'false', 'error' => 'Device not found']);
        }
    
        $endpoint_data = ClientController::getClientsFromProviders();

        if($endpoint_data == null or empty($endpoint_data)) {
            return json_encode(['success' => 'false', 'error' => 'Client provider returned no data']);
 
        }

        $DataToIds = [];
        $MacsToIds = [];
        $i = 0;
        $macTable = (isset($device->mac_table_data)) ? json_decode($device->mac_table_data, true) : [];
        $macData = (isset($macTable)) ? $macTable : [];

        if(count($macData) == 0) {
            return json_encode(['success' => 'false', 'error' => 'No data from device']);
        }

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

    static function checkOnlineStatus() {
        $clients = Client::all();

        $start = microtime(true);
        $clients2 = [];
        $ipc = 15;
        for($i = 0; $i < count($clients); $i++) {
            if($clients[$i]->ip_address == "") {
                $try = gethostbyname($clients[$i]->hostname);
                if($try != $clients[$i]->hostname) {
                    $clients[$i]->ip_address = $try;
                    $clients[$i]->save();
                } else {
                    $clients2[$i] = "12.13.14.".$ipc;
                    $ipc++;
                }
            } else {
                $clients2[$i] = $clients[$i]->ip_address;
            }
        }

        $pingclients = implode(" ", $clients2);
        $result = exec("fping -i 50 ".$pingclients." 2> /dev/null", $output, $return);

        foreach($output as $client) {
            $data = explode(" ", $client);
            $key = array_search($data[0], $clients2);
            if($key !== false or $key == 0) {
                // $key = array_search($data[0], $clients2);
                if($data[0] == "12.13.14.15") {
                    $clients[$key]->online = 0;
                }

                if($data[2] == "alive") {
                    $clients[$key]->online = 1;
                } else {
                    $clients[$key]->online = 0;
                }

                if($clients[$key]->created_at->diffInDays(Carbon::now()) > 7) {
                    $clients[$key]->online = 2;
                }

                echo $clients[$key]->hostname . " | " . $clients2[$key]." | ". $clients[$key]->online."\n";


                $clients[$key]->save();
            }
        }

        $elapsed = microtime(true) - $start;
        echo ($elapsed."sec");
    }
}
