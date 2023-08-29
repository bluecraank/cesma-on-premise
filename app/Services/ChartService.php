<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Device;
use App\Models\DevicePort;
use App\Models\DeviceVlan;
use App\Models\DeviceVlanPort;
use App\Models\Vlan;

class ChartService
{
    public static function portsToVlans()
    {
        $portsToVlans = DeviceVlanPort::all()->groupBy('device_vlan_id')->toArray();
        $allDeviceVlans = DeviceVlan::all()->keyBy('id')->toArray();
        $vlans = Vlan::all()->keyBy('vid')->toArray();
        $vlanToPorts = [];

        if(count($vlans) == 0 || count($portsToVlans) == 0 || count($allDeviceVlans) == 0) {
            return [["No vlans"], [0]];
        }

        foreach($portsToVlans as $device_vlan_id => $ports) {
            $vlan_id = $allDeviceVlans[$device_vlan_id]['vlan_id'];
            $key_name = $vlans[$vlan_id]['name']." (".$allDeviceVlans[$device_vlan_id]['vlan_id'].")";

            if(!isset($vlanToPorts[$key_name])) {
                $vlanToPorts[$key_name] = [];
            }

            $vlanToPorts[$key_name] = array_merge($vlanToPorts[$key_name], array_keys($ports));
        }

        $vlanToPorts["Everything else"] = 0;

        if(count($vlans) >= 30) {
            $ignoreCount = 10;
        } elseif(count($vlans) >= 20) {
            $ignoreCount = 5;
        } elseif(count($vlans) >= 10) {
            $ignoreCount = 2;
        } else {
            $ignoreCount = 0;
        }

        foreach($vlanToPorts as $vlan_id => $ports) {
            if($vlan_id == "Everything else") {
                continue;
            }

            $vlanToPorts[$vlan_id] = count($ports);
            if(count($ports) == 0 || count($ports) <= $ignoreCount) {
                $vlanToPorts["Everything else"] += count($ports);
            }
        }

        $keys = array_keys($vlanToPorts);
        $values = array_values($vlanToPorts);

        return [$keys, $values];
    }

    public static function clientsToVlans() {
        $clients = Client::all()->groupBy('vlan_id')->toArray();
        $vlans = Vlan::all()->keyBy('vid')->toArray();

        if(count($vlans) == 0 || count($clients) == 0) {
            return [["No vlans"], [0]];
        }

        $vlanToClients = [];

        foreach($clients as $vlan_id => $all_clients) {
            $name = $vlans[$vlan_id]['name']." (".$vlan_id.")";

            if(!isset($vlanToClients[$vlan_id])) {
                $vlanToClients[$name] = [];
            }

            $vlanToClients[$name] = array_merge($vlanToClients[$name], $clients[$vlan_id]);
        }

        foreach($vlanToClients as $vlan_id => $clients) {
            $vlanToClients[$vlan_id] = count($clients);
        }

        $keys = array_keys($vlanToClients);
        $values = array_values($vlanToClients);

        return [$keys, $values] ;
    }

    public static function portsOnline() {
        $ports = DevicePort::where('link', 1)->count();
        $ports_offline = DevicePort::where('link', 0)->count();

        if($ports == 0 && $ports_offline == 0) {
            return [["No ports"], [0]];
        }

        $keys = ["Online", "Offline"];
        $values = [$ports, $ports_offline];

        return [$keys, $values];
    }

    public static function devicesOnline() {
        $devices = Device::all();

        $count = $devices->count();
        $online = 0;
        foreach($devices as $device) {
            $device->active() ? $online++ : null;
        }

        return [$online, $count];
    }
}
