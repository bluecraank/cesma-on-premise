<?php

namespace App\Services;

use App\Models\DeviceVlan;
use App\Models\DeviceVlanPort;

class ChartService
{
    public static function portsToVlans($devices, $vlans)
    {
        $portsToVlans = DeviceVlanPort::whereIn('device_id', $devices->pluck('id'))->where('is_tagged', false)->get()->groupBy('device_vlan_id')->toArray();
        $allDeviceVlans = DeviceVlan::whereIn('device_id', $devices->pluck('id'))->get()->keyBy('id')->toArray();
        $vlans = $vlans->keyBy('vid')->toArray();

        $vlanToPorts = [];

        if(count($vlans) == 0 || count($portsToVlans) == 0 || count($allDeviceVlans) == 0) {
            return [["No vlans"], [0]];
        }

        foreach($portsToVlans as $device_vlan_id => $ports) {
            $vlan_id = $allDeviceVlans[$device_vlan_id]['vlan_id'];
            $key_name = $vlans[$vlan_id]['name']." (".$allDeviceVlans[$device_vlan_id]['vlan_id'].")";

            if(!isset($vlanToPorts[$key_name])) {
                $vlanToPorts[$key_name] = 0;
            }

            $vlanToPorts[$key_name] += count(array_keys($ports));
        }

        $keys = array_keys($vlanToPorts);
        $values = array_values($vlanToPorts);

        return [$keys, $values];
    }

    public static function clientsToVlans($clients, $vlans) {
        $clients = $clients->groupBy('vlan_id')->toArray();
        $vlans = $vlans->keyBy('vid')->toArray();

        if(count($vlans) == 0 || count($clients) == 0) {
            return [["No vlans"], [0]];
        }

        $vlanToClients = [];

        foreach($clients as $vlan_id => $all_clients) {

            if(!isset($vlans[$vlan_id])) {
                continue;
            }

            $name = $vlans[$vlan_id]['name']." (".$vlan_id.")";

            if(!isset($vlanToClients[$vlan_id])) {
                $vlanToClients[$name] = 0;
            }

            $vlanToClients[$name] += count($clients[$vlan_id]);
        }

        $keys = array_keys($vlanToClients);
        $values = array_values($vlanToClients);

        return [$keys, $values] ;
    }

    public static function portsOnline($ports) {
        $ports_online = $ports->where('link', 1)->count();
        $ports_offline = $ports->where('link', 0)->count();

        if($ports_online == 0 && $ports_offline == 0) {
            return [["No ports"], [0]];
        }

        $keys = ["Online", "Offline"];
        $values = [$ports_online, $ports_offline];

        return [$keys, $values];
    }

    public static function devicesOnline($devices) {
        $count = $devices->count();
        $online = 0;
        foreach($devices as $device) {
            $device->active() ? $online++ : null;
        }

        return [$online, $count];
    }
}
