<?php

namespace App\Services;

use App\Models\MacType;
use App\Models\MacTypeIcon;
use App\Models\Client;
use App\Models\Device;
use App\Models\DeviceUplink;
use App\Models\Mac;
use App\Models\Vlan;
use App\Models\SnmpMacData;
use Illuminate\Support\Facades\Log;

class ClientService {
    /*
    * Get all clients from all providers
    * @return array
    */
    static function getClients() {
        $start = microtime(true);
        
        $macs = Mac::where('type', '!=', 'uplink')->orWhereNull('type')->get()->keyBy('mac_address')->toArray();
        $endpoints = SnmpMacData::all();
        $devices = Device::all()->keyBy('id')->toArray();
        $uplinks = DeviceUplink::all()->keyBy('id')->groupBy('device_id')->toArray();

        // TODO: Pluck VlanID führt dazu, dass verschiedene Standorte so nicht gehen. Clients dürfen nicht die VlanID als Attribut haben, sondern die ID des Vlans in aus der DB
        $vlans = Vlan::where('is_client_vlan', false)->pluck('vid')->toArray();

        foreach($uplinks as $dev_id => $uplink) {
            foreach($uplink as $each_uplink) {
                $array_uplinks[$dev_id][] = $each_uplink['name']; 
            }
        }

        $updated = $created = 0;
        $mac_already_added = [];

        foreach($endpoints as $client) {
            $mac = $client['mac_address'];
            
            if(empty($mac) || $mac == "") {
                continue;
            }

            // MAC Adresse nicht vorhanden in Mac-Datenbank
            if(!array_key_exists($mac, $macs)) {
                continue;
            }

            // Daten zu dem Gerät nicht vorhanden
            if(!isset($devices[$macs[$mac]['device_id']])) {
                continue;
            }

            // Gerät wurde an einem Uplink Port gefunden, ignorieren
            if(isset($array_uplinks[$macs[$mac]['device_id']]) && in_array($macs[$mac]['port_id'], $array_uplinks[$macs[$mac]['device_id']])) {
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

            $DbClient = Client::updateOrCreate([
                'id' => md5($mac.$client['hostname']),
            ], 
            [
                'mac_address' => $mac,
                'port_id' => $macs[$mac]['port_id'],
                'device_id' => $macs[$mac]['device_id'],
                'vlan_id' => $macs[$mac]['vlan_id'],
                'ip_address' => $client['ip_address'],
                'type' => self::getClientType($mac),
                'site_id' => $devices[$macs[$mac]['device_id']]['site_id'],
            ]);

            if($DbClient->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        Log::info("[Clients] Updated {$updated} and created {$created} clients.");
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
