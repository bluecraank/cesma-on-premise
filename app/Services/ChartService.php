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

        foreach($portsToVlans as $device_vlan_id => $ports) {
            $vlan_id = $allDeviceVlans[$device_vlan_id]['vlan_id'];
            $key_name = $vlans[$vlan_id]['name']." (".$allDeviceVlans[$device_vlan_id]['vlan_id'].")";

            if(!isset($vlanToPorts[$vlan_id])) {
                $vlanToPorts[$key_name] = [];
            }

            $vlanToPorts[$key_name] = array_merge($vlanToPorts[$key_name], array_keys($portsToVlans[$device_vlan_id]));
        }

        foreach($vlanToPorts as $vlan_id => $ports) {
            $vlanToPorts[$vlan_id] = count($ports);
        }

        $keys = array_keys($vlanToPorts);
        $values = array_values($vlanToPorts);

        return [$keys, $values];
    }

    public static function clientsToVlans() {
        $clients = Client::all()->groupBy('vlan_id')->toArray();
        $vlans = Vlan::all()->keyBy('vid')->toArray();


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
