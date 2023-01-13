<?php

namespace App\Http\Controllers;

use App\ClientProviders\Baramundi;
use App\ClientProviders\SNMP_Routers;
use App\Models\Device;
use App\Models\Client;
use App\Models\MacAddress;
use App\Models\UnknownClient;
use Carbon\Carbon;
class ClientController extends Controller
{

    public function index() {
        $clients = Client::where('vlan_id', '!=', 3056)->get();
        $devices = Device::all()->keyBy('id');

        return view('client.index', compact('clients', 'devices'));
    }

    static function getClientsFromProviders() { 
        // Baramundi
        if(!empty(config('app.baramundi_api_url'))) {
            $provider = new Baramundi;
            $endpoints = $provider->queryClientData();
        
        }

        $data = [];
        // Sophos XG
        $data = SNMP_Routers::queryClientData();

        // Returns: Array of mac_addresses, hostname and ip_address
        return array_merge($endpoints, $data);
    }

    static function getClientsAllDevices() {
        $endpoints = ClientController::getClientsFromProviders();

        if($endpoints == null or empty($endpoints)) {
            return dd("Keine Endpoints der Provider erhalten");
        }
        
        $macs = MacAddress::all()->sortBy('vlan_id');

        $mac_only = [];
        $mac_data = [];

        $i = 0;
        foreach($macs as $mac) {
            if($key = array_search($mac->mac_address, $mac_only)) {
                if($mac_data[$key]['vlan_id'] > $mac->vlan_id) {
                    $mac_data[$key] = [
                        'mac_address' => $mac->mac_address,
                        'device_id' => $mac->device_id,
                        'port_id' => $mac->port_id,
                        'vlan_id' => $mac->vlan_id,
                    ];
                    $mac_only[$key] = $mac->mac_address;
                }
            } else {
                $mac_only[$i] = $mac->mac_address;
                $mac_data[$i] = [
                    'mac_address' => $mac->mac_address,
                    'device_id' => $mac->device_id,
                    'port_id' => $mac->port_id,
                    'vlan_id' => $mac->vlan_id,
                ];
                $i++;

            }
        }

        $unique_endpoints = [];

        foreach($endpoints as $client) {
            foreach($client['mac_addresses'] as $mac) {
                if($key = array_search($mac, $mac_only)) {
                    $unique_endpoints[$key] = [
                        'hostname' => $client['hostname'],
                        'ip_address' => $client['ip_address'],
                        'mac_address' => $mac,
                        'device_id' => $mac_data[$key]['device_id'],
                        'port_id' => $mac_data[$key]['port_id'],
                        'vlan_id' => $mac_data[$key]['vlan_id'],
                        'type' => "client",
                    ];
                }
            }
        }

        // Drucker
        $printer_macs = [
            '00206b'
        ];
        // Telefone
        $phone_macs = [
            '08000f',
            '1400e9',
            '00085d',
        ];
        
        // Example:
        // "hostname" => "pbx-nor.doepke.local."
        // "ip_address" => "192.168.120.1"
        // "mac_address" => "00085d9a56a0"
        // "device_id" => 1
        // "port_id" => "F20"
        // "vlan_id" => "120"
        // "type" => "phone"

        foreach($unique_endpoints as $key => $client) {
            $result = mb_substr($client['mac_address'], 0, 6);
            if(in_array($result, $printer_macs)) {
                $type = "printer";
                echo $client['mac_address']." | ".$type."<br>";
                $unique_endpoints[$key]['type'] = $type;
            } elseif(in_array($result, $phone_macs)) {
                $type = "phone";
                echo $client['mac_address']." | ".$type."<br>";

                $unique_endpoints[$key]['type'] = $type;
            }
        }

        foreach($unique_endpoints as $key => $client) {
            $md5 = md5($client['hostname'].$client['ip_address']);
            $found = Client::where('id', md5($client['hostname'].$client['ip_address']))->orWhere('mac_address', $client['mac_address'])->first();
            if($found) {
                $found->update([
                    'hostname' => $client['hostname'],
                    'ip_address' => $client['ip_address'],
                    'device_id' => $client['device_id'],
                    'port_id' => $client['port_id'],
                    'vlan_id' => $client['vlan_id'],
                    'type' => $client['type'],
                ]);
            } else {
                $client = Client::create([
                    'id' => $md5,
                    'hostname' => $client['hostname'],
                    'ip_address' => $client['ip_address'],
                    'mac_address' => $client['mac_address'],
                    'switch_id' => $client['device_id'],
                    'port_id' => $client['port_id'],
                    'vlan_id' => $client['vlan_id'],
                    'type' => $client['type'],
                ]);
            }
        }

        // echo "MACs: ".$i." | MACs found on Switch: ".$is. " | Already saved: ".$found." | New: ".$new ."\n";
        return json_encode(['success' => 'true', 'error' => 'Clients updated']);
    }

    static function debugMacTable() {
        $endpoints = ClientController::getClientsFromProviders();

        if($endpoints == null or empty($endpoints)) {
            return dd("Keine Endpoints der Provider erhalten");
        }
        
        $macs = MacAddress::all()->sortBy('vlan_id');

        $mac_only = [];
        $mac_data = [];

        $i = 0;
        foreach($macs as $mac) {
            if($key = array_search($mac->mac_address, $mac_only)) {
                if($mac_data[$key]['vlan_id'] > $mac->vlan_id) {
                    $mac_data[$key] = [
                        'mac_address' => $mac->mac_address,
                        'device_id' => $mac->device_id,
                        'port_id' => $mac->port_id,
                        'vlan_id' => $mac->vlan_id,
                    ];
                    $mac_only[$key] = $mac->mac_address;
                }
            } else {
                $mac_only[$i] = $mac->mac_address;
                $mac_data[$i] = [
                    'mac_address' => $mac->mac_address,
                    'device_id' => $mac->device_id,
                    'port_id' => $mac->port_id,
                    'vlan_id' => $mac->vlan_id,
                ];
                $i++;

            }
        }

        $unique_endpoints = [];
        foreach($endpoints as $client) {
            foreach($client['mac_addresses'] as $mac) {
                if($key = array_search($mac, $mac_only)) {
                    $unique_endpoints[$key] = [
                        'hostname' => $client['hostname'],
                        'ip_address' => $client['ip_address'],
                        'mac_address' => $mac,
                        'device_id' => $mac_data[$key]['device_id'],
                        'port_id' => $mac_data[$key]['port_id'],
                        'vlan_id' => $mac_data[$key]['vlan_id'],
                    ];
                }
            }
        }

        return dd($unique_endpoints);
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
