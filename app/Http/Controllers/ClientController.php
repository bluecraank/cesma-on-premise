<?php

namespace App\Http\Controllers;

use App\ClientProviders\Baramundi;
use App\ClientProviders\SNMP_Routers;
use App\Models\Device;
use App\Models\Client;
use App\Models\MacAddress;
use App\Models\MacTypeFilter;
use App\Models\MacTypeIcon;
use App\Models\UplinkClient;
use App\Models\Vlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{

    public function index()
    {

        $clients = Client::where('vlan_id', '!=', 3056)->get();
        $devices = Device::all()->keyBy('id');

        return view('client.client-overview', compact('clients', 'devices'));
    }

    static function getClientDataFromProviders()
    {
        // Baramundi
        if (!empty(config('app.baramundi_api_url'))) {
            $provider = new Baramundi;
            $endpoints = $provider->queryClientData();
        }

        // Routers
        $data = SNMP_Routers::queryClientData();

        $merged = array_merge($endpoints, $data);

        if ($merged == null or empty($merged)) {
            return false;
        }

        // Sort endpoints by ip address
        usort($merged, function ($a, $b) {
            return ip2long($b['ip_address']) <=> ip2long($a['ip_address']);
        });

        return $merged;
    }

    static function getClientsAllDevices()
    {
        $start = microtime(true);
        $created = 0;
        $updated = 0;

        $devices = Device::all()->keyBy('id');
        $ip_of_devices = [];

        foreach ($devices as $device) {
            $ip_of_devices[$device['id']] = gethostbyname($device['hostname']);
        }

        // Get vlans that are not client vlans
        $vlans = Vlan::where('is_client_vlan', false)->get()->keyBy('vid')->toArray();

        // Get all clients from providers
        $endpoints = ClientController::getClientDataFromProviders();
        if (!$endpoints) {
            Log::error("No clients received from providers");
            return false;
        }

        // Get all mac addresses from database
        // descending sort by device_id because newer devices are more likely at the end of star topology
        $mactable = MacAddress::all()->sortBy('vlan_id')->keyBy('mac_address');
        $unique_endpoints = [];

        // Get unique endpoints based on mac address
        foreach ($endpoints as $client) {
            foreach ($client['mac_addresses'] as $mac) {

                // Check if mac address is in mactable and not in ignored vlans
                if (!isset($unique_endpoints[$mac]) && isset($mactable[$mac]) and !array_key_exists($mactable[$mac]['vlan_id'], $vlans) and !str_contains($mactable[$mac]['port_id'], "UPLINK-")) {
                    $unique_endpoints[$mac] = true;

                    $insert_data = [
                        'hostname' => $client['hostname'],
                        'ip_address' => $client['ip_address'],
                        'switch_id' => $mactable[$mac]['device_id'],
                        'port_id' => $mactable[$mac]['port_id'],
                        'vlan_id' => $mactable[$mac]['vlan_id'],
                        'type' => self::getClientType($mac),
                    ];

                    // Check if client already exists in database
                    $client_in_db = Client::where('mac_address', $mac)->first();
                    if ($client_in_db) {
                        $client_in_db->update($insert_data);
                        $updated++;
                    } else {
                        $insert_data['id'] = $mac;
                        $insert_data['mac_address'] = $mac;

                        if (Client::create($insert_data)) {
                            $created++;
                            Log::info('New client: ' . $mac . ' (' . $client['hostname'] . ')');
                        } else {
                            Log::error('Error creating client: ' . $mac . ' (' . $client['hostname'] . ')');
                        }
                    }
                }

                // // Uplink Clients
                // if (!isset($unique_endpoints[$mac]) && isset($mactable[$mac]) and !array_key_exists($mactable[$mac]['vlan_id'], $vlans) and str_contains($mactable[$mac]['port_id'], "UPLINK-")) {
                //     if (in_array($client['ip_address'], $ip_of_devices)) {
                //         UplinkClient::updateOrCreate(
                //             [
                //                 'mac_address' => $mac,
                //                 'switch_id' => $mactable[$mac]['device_id'],

                //             ],
                //             [
                //                 'hostname' => $client['hostname'],
                //                 'ip_address' => $client['ip_address'],
                //                 'port_id' => str_replace("UPLINK-","", $mactable[$mac]['port_id']),
                //                 'vlan_id' => $mactable[$mac]['vlan_id'],
                //             ]
                //         );
                //         Log::info('Delete' . $client['hostname'] . ' (' . $client['ip_address'] . ')');
                //     }
                // }
            }
        }

        Log::info('Clients successfully updated (New:' . $created . ' Updated:' . $updated . ') (' . number_format(microtime(true) - $start, 2) . 's)');
    }

    static function getClientType($mac)
    {
        $types = MacTypeFilter::all()->keyBy('mac_prefix')->toArray();

        $mac_prefix = substr($mac, 0, 6);
        if (array_key_exists($mac_prefix, $types)) {
            return $types[$mac_prefix]['mac_type'];
        }

        return 'client';
    }

    static function getClientIcon($type)
    {
        $icons = MacTypeIcon::all()->keyBy('mac_type')->toArray();

        if (array_key_exists($type, $icons)) {
            return "fas " . $icons[$type]['mac_icon'];
        }

        return 'fas fa-desktop';
    }

    static function checkOnlineStatus()
    {
        $clients = Client::all()->keyBy('id');

        $start = microtime(true);

        $clients_ips = [];
        foreach ($clients as $key => $client) {
            $clients_ips[$key] = $client->ip_address;
        }

        $client_ip_addresses = implode(" ", $clients_ips);

        $result = exec("fping -i 50 " . $client_ip_addresses . " 2> /dev/null", $output, $return);

        foreach ($output as $client) {
            $data = explode(" ", $client);
            $key = array_search($data[0], $clients_ips);
            if ($key !== false or $key == 0) {
                if ($data[2] == "alive") {
                    $clients[$key]->online = 1;
                } else {
                    $clients[$key]->online = 0;
                }

                if ($clients[$key]->created_at->diffInDays(Carbon::now()) > 7) {
                    $clients[$key]->online = 2;
                }

                $clients[$key]->save();
            }
        }

        $elapsed = microtime(true) - $start;

        Log::info('Clients pinged in ' . $elapsed . " seconds");
    }

    static function deleteClientsOnUplinks($device)
    {
        Client::where('switch_id', $device->id)->where(function ($query) use ($device) {
            $uplinks = json_decode($device->uplinks, true) ?? [];
            foreach ($uplinks as $uplink) {
                $query->orWhere('port_id', $uplink);
            }
        })->delete();
    }
}
