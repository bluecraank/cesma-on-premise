<?php

namespace App\Http\Controllers;

use App\ClientProviders\Baramundi;
use App\ClientProviders\SNMP_Routers;
use App\Models\Device;
use App\Models\Client;
use App\Models\MacAddress;
use Carbon\Carbon;

class ClientController extends Controller
{
    public function index() {
        $clients = Client::where('vlan_id', '!=', 3056)->get();
        $devices = Device::all()->keyBy('id');

        return view('client.index', compact('clients', 'devices'));
    }

    static function getClientDataFromProviders() { 
        // Baramundi
        if(!empty(config('app.baramundi_api_url'))) {
            $provider = new Baramundi;
            $endpoints = $provider->queryClientData();
        }

        // Routers
        $data = SNMP_Routers::queryClientData();

        $merged = array_merge($endpoints, $data);

        if($merged == null or empty($merged)) {
            return false;
        }

        return array_merge($endpoints, $data);
    }

    static function getClientsAllDevices() {
        $start = microtime(true);
        // Get all clients from providers
        $endpoints = ClientController::getClientDataFromProviders() ?? dd("Keine Endpoints der Provider erhalten");
        
        // Get all mac addresses from database
        // lower vlans are more likely to be correct
        // descending sort by device_id because newer devices are more likely at the end of star topology
        $mactable = MacAddress::all()->sortBy('vlan_id')->sortByDesc('device_id')->keyBy('mac_address');
        $unique_endpoints = [];

        // Get unique endpoints based on mac address
        foreach($endpoints as $client) {
            foreach($client['mac_addresses'] as $mac) {
                if(!isset($unique_endpoints[$mac]) && isset($mactable[$mac])) {
                    $unique_endpoints[$mac] = true;

                    $insert_data = [
                        'hostname' => $client['hostname'],
                        'ip_address' => $client['ip_address'],
                        'port_id' => $mactable[$mac]['port_id'],
                        'vlan_id' => $mactable[$mac]['vlan_id'],
                        'type' => self::getClientType($mac),
                    ];

                    $identifier = md5($client['hostname'].$client['ip_address']);

                    // Check if client already exists in database
                    $client_in_db = Client::where('id', $identifier)->orWhere('mac_address', $mac)->first();
                    if($client_in_db) {
                        $client_in_db->update($insert_data);
                    } else {
                        $insert_data['id'] = $identifier;
                        $insert_data['mac_address'] = $mac;
                        $insert_data['switch_id'] = $mactable[$mac]['device_id'];
                        Client::create($insert_data);
                    }
                }
            }
        }

        return json_encode(['success' => 'true', 'error' => 'Clients successfully updated ('.number_format(microtime(true) - $start, 2).'s)']);
    }

    static function getClientType($mac) {
        $type = "client";
            
        $phone_macs = explode(",", config('app.phone_macs'));
        $printer_macs = explode(",", config('app.printer_macs'));

        $result = mb_substr($mac, 0, 6);

        if(in_array($result, $printer_macs)) {
            $type = "printer";
        } elseif(in_array($result, $phone_macs)) {
            $type = "phone";
        }

        return $type;
    }

    static function checkOnlineStatus() {
        $clients = Client::all()->keyBy('id');

        $start = microtime(true);

        $clients_ips = [];
        foreach($clients as $key => $client) {
            $clients_ips[$key] = $client->ip_address;
        }

        $client_ip_addresses = implode(" ", $clients_ips);

        $result = exec("fping -i 50 ".$client_ip_addresses." 2> /dev/null", $output, $return);

        foreach($output as $client) {
            $data = explode(" ", $client);
            $key = array_search($data[0], $clients_ips);
            if($key !== false or $key == 0) {
                if($data[2] == "alive") {
                    $clients[$key]->online = 1;
                } else {
                    $clients[$key]->online = 0;
                }
                
                if($clients[$key]->created_at->diffInDays(Carbon::now()) > 7) {
                    $clients[$key]->online = 2;
                }

                $clients[$key]->save();
            }
        }

        $elapsed = microtime(true) - $start;
        dd('Clients pinged in '.$elapsed." seconds");
    }
}
