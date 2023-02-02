<?php

namespace App\Services;

use App\ClientProviders\Baramundi;
use App\ClientProviders\SNMP_Routers;
use App\Http\Controllers\ClientController;
use App\Models\MacType;
use App\Models\MacTypeIcon;
use App\Models\Client;
use App\Models\Device;
use App\Models\DeviceCustomUplink;
use App\Models\DeviceUplink;
use App\Models\Mac;
use App\Models\MacVendor;
use App\Models\Vlan;
use Illuminate\Support\Facades\Log;

class ClientService {
    static function getClientDataFromProviders()
    {
        $start = microtime(true);
        // Baramundi
        if (!empty(config('app.baramundi_api_url'))) {
            $provider = new Baramundi;
            $endpoints = $provider->queryClientData();
        }
        echo "Baramundi: " . (microtime(true) - $start) . "s<br>";

        // Routers
        $start = microtime(true);
        $data = SNMP_Routers::queryClientData();
        echo "Routers: " . (microtime(true) - $start) . "s<br>";

        $start = microtime(true);
        $merged = array_merge($endpoints, $data);

        if ($merged == null or empty($merged)) {
            return false;
        }
        echo "Merge: " . (microtime(true) - $start) . "s<br>";

        $start = microtime(true);
        // Sort endpoints by ip address
        usort($merged, function ($a, $b) {
            return ip2long($b['ip_address']) <=> ip2long($a['ip_address']);
        });
        echo "Sort: " . (microtime(true) - $start) . "s<br>";

        return $merged;
    }


    static function getClients() {
        $start = microtime(true);
        $macs = Mac::all()->keyBy('mac_address')->toArray();
        $endpoints = self::getClientDataFromProviders();
        $devices = Device::all()->keyBy('id')->toArray();
        $uplinks = DeviceUplink::all()->keyBy('id')->groupBy('device_id')->toArray();
        $custom_uplinks = DeviceCustomUplink::all()->keyBy('device_id')->toArray();
        $vlans = Vlan::where('is_client_vlan', false)->pluck('vid')->toArray();

        $array_uplinks = [];
        foreach($custom_uplinks as $dev_id => $custom_uplink) {
            $array_uplinks[$dev_id] = json_decode($custom_uplink['uplinks'], true);
        }

        foreach($uplinks as $dev_id => $uplink) {
            foreach($uplink as $up) {
                $array_uplinks[$dev_id][] = $up['name']; 
            }
        }

        $updated = 0;
        $created = 0;
        $mac_already_added = [];
        foreach($endpoints as $client) {
            foreach($client['mac_addresses'] as $mac) {
                // MAC Adresse nicht vorhanden in Mac-Datenbank
                if(!array_key_exists($mac, $macs)) {
                    continue;
                }

                // Daten zu dem Gerät nicht vorhanden
                if(!isset($devices[$macs[$mac]['device_id']])) {
                    continue;
                }

                // Gerät wurde an einem Uplink Port gefunden, ignorieren
                if(in_array($macs[$mac]['port_id'], $array_uplinks[$macs[$mac]['device_id']])) {
                    continue;
                }
                
                // VLAN ist kein Client VLAN
                if(in_array($macs[$mac]['vlan_id'], $vlans)) {
                    continue;
                }

                // Wurde bereits durchlaufen
                if(isset($mac_already_added[$mac])) {
                    continue;
                }

                $mac_already_added[$mac] = true;
                
                $client = Client::updateOrCreate([
                    'id' => md5($mac.$client['hostname']),
                    'mac_address' => $mac
                ], 
                [
                    'port_id' => $macs[$mac]['port_id'],
                    'device_id' => $macs[$mac]['device_id'],
                    'vlan_id' => $macs[$mac]['vlan_id'],
                    'hostname' => $client['hostname'],
                    'ip_address' => $client['ip_address'],
                    'type' => self::getClientType($mac),
                ]);

                if($client->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }
        }

        Log::info('Updated '. $updated .' clients | Created '. $created .' clients | Took: '. (microtime(true) - $start) .'s');
    } 

    static function getClientType($mac)
    {
        $types = MacType::all()->keyBy('mac_prefix')->toArray();

        $mac_prefix = substr($mac, 0, 6);
        if (array_key_exists($mac_prefix, $types)) {
            return $types[$mac_prefix]['id'];
        }

        return 0;
    }

    static function getClientIcon($type)
    {
        $icons = MacTypeIcon::all()->keyBy('mac_type_id')->toArray();
        // dd($type, $icons);
        if (array_key_exists($type, $icons)) {
            return "fas " . $icons[$type]['mac_icon'];
        }

        return 'fas fa-desktop';
    }

}
