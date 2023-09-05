<?php

namespace App\Services;

use App\Models\DevicePort;
use App\Models\DevicePortStat;
use App\Models\DeviceVlan;
use App\Models\DeviceVlanPort;
use App\Models\Mac;
use App\Models\SnmpMacData;
use App\Http\Controllers\NotificationController as Notification;
use App\Models\DeviceUplink;

class UpdateDeviceData
{
    static function updateDevicePorts($ports, $device)
    {
        $existingPorts = $device->ports()->get(['name', 'link', 'speed'])->keyBy('name')->toArray();

        // Update/Create ports
        foreach ($ports as $port) {

            $snmp_if_index = $port['snmp_if_index'] ?? (isset($existingPorts[$port['name']]) ? $existingPorts[$port['name']]['snmp_if_index'] : null);

            DevicePort::updateOrCreate(
                [
                    'name' => $port['id'],
                    'device_id' => $device->id
                ],
                [
                    'description' => $port['name'],
                    'link' => $port['link'],
                    'speed' => $port['speed'] ?? 0,
                    'vlan_mode' => $port['vlan_mode'],
                    'snmp_if_index' => $snmp_if_index,
                ]
            );

            // Notify on link status change
            if (isset($existingPorts[$port['id']]) && $existingPorts[$port['id']]['link'] != $port['link']) {
                DevicePort::where('name', $port['id'])->first()->touch('last_admin_status');
                Notification::link_change($port['id'], $device, $port['link']);
            }

            // Notify on speed change only if port up
            if (isset($existingPorts[$port['id']]) && $port['link'] && isset($port['speed']) && $port['speed'] != 0 && $existingPorts[$port['id']]['speed'] != $port['speed']) {
                Notification::speed_change($port['id'], $device, $port['speed'], $existingPorts[$port['id']]['speed']);
            }

            $existingPorts[$port['id']] = true;
        }
    }

    static function updateDeviceVlans($vlans, $device)
    {
        // Update/Create vlans
        foreach ($vlans as $vid => $vname) {
            $device->vlans()->updateOrCreate(
                [
                    'vlan_id' => $vid,
                    'device_id' => $device->id
                ],
                [
                    'name' => $vname,
                ]
            );

            // Save as global vlan
            VlanService::createIfNotExists($device, $vid, $vname);
        }

        // Delete vlans that are not in the list
        DeviceVlan::where('device_id', $device->id)->where(function ($query) use ($vlans) {
            foreach ($vlans as $vid => $unused) {
                $query->where('vlan_id', '!=', $vid);
            }
        })->delete();
    }

    static function updateVlanPorts($vlanports, $device)
    {
        $return = ['uplinks' => []];

        // $currentVlanPorts = DeviceVlanPort::where('device_id', $device->id)->get()->toArray();
        $ports = $device->ports()->get()->keyBy('name')->toArray();
        $vlans = $device->vlans()->get()->keyBy('vlan_id')->toArray();

        $stillExists = [];
        // Update/Create vlan ports and find uplinks
        foreach ($vlanports as $vlanport) {
            if ($vlanport['vlan_id'] == "Trunk") {
                $return['uplinks'][$vlanport['port_id']] = $vlanport['port_id'];
            } else {
                $device_port = $ports[$vlanport['port_id']] ?? null;
                $device_vlan = $vlans[$vlanport['vlan_id']] ?? null;

                if (!$device_port || !$device_vlan) {
                    continue;
                }

                $got = DeviceVlanPort::updateOrCreate(
                    [
                        'device_port_id' => $device_port['id'],
                        'device_id' => $device->id,
                        'device_vlan_id' => $device_vlan['id'],
                        'is_tagged' => $vlanport['is_tagged']
                    ]
                );

                $stillExists[$got->id] = true;
            }
        }

        DeviceVlanPort::where('device_id', $device->id)->whereNotIn('id', array_keys($stillExists))->delete();

        return $return;
    }

    static function updateDeviceUplinks($uplinks, $new_uplinks, $device)
    {

        $current_uplinks = $device->uplinks()->get()->keyBy('name');
        foreach ($uplinks as $port => $trunk_group) {
            if (isset($current_uplinks[$trunk_group])) {
                if (is_array($current_uplinks[$trunk_group]->ports))
                    $ports = $current_uplinks[$trunk_group]->ports;
                elseif (!empty($current_uplinks[$trunk_group]->ports))
                    $ports = json_decode($current_uplinks[$trunk_group]->ports, true);
                else
                    $ports = [];

                $current_uplinks[$trunk_group]->ports = array_merge($ports, [$port]);
                $current_uplinks[$trunk_group]->ports = array_unique($current_uplinks[$trunk_group]->ports);
                $current_uplinks[$trunk_group]->save();
            }
        }

        return $uplinks;
    }

    static function updateDevicePortStatistics($statistics, $device)
    {
        foreach ($statistics as $statistic) {
            // Skip if no id is set
            if (!isset($statistic['id']) or !$statistic['id']) {
                continue;
            }

            // Get port id
            $port = $device->ports()->where('name', $statistic['id'])->first();

            // Create port statistic
            if ($port) {
                $id = $port->id;
                DevicePortStat::create([
                    'device_port_id' => $id,
                    'port_speed' => $statistic['port_speed_mbps'] ?? 0,
                    'port_status' => $statistic['port_status'] ?? false,
                    'port_rx_bps' => $statistic['port_rx_bps'] ?? 0,
                    'port_tx_bps' => $statistic['port_tx_bps'] ?? 0,
                    'port_rx_pps' => $statistic['port_rx_pps'] ?? 0,
                    'port_tx_pps' => $statistic['port_tx_pps'] ?? 0,
                    'port_rx_bytes' => $statistic['port_rx_bytes'] ?? 0,
                    'port_tx_bytes' => $statistic['port_tx_bytes'] ?? 0,
                    'port_rx_packets' => $statistic['port_rx_packets'] ?? 0,
                    'port_tx_packets' => $statistic['port_tx_packets'] ?? 0,
                    'port_rx_errors' => $statistic['port_rx_errors'] ?? 0,
                    'port_tx_errors' => $statistic['port_tx_errors'] ?? 0
                ]);
            }
        }
    }

    static function updateMacData($macs, $combined_uplinks, $device)
    {
        foreach ($macs as $mac) {
            $combined_uplinks[0] = 0;

            // Do not store macs on uplinks because its not correct discovered
            if (in_array($mac['port'], $combined_uplinks)) {
                // Store uplink mac address
                Mac::updateOrCreate(
                    [
                        'mac_address' => $mac['mac'],
                        'type' => 'uplink'
                    ],
                    [
                        'device_id' => $device->id,
                        'port_id' => $mac['port'],
                        'vlan_id' => $mac['vlan'],
                    ]
                );

                // Prevent storing same mac address twice
                continue;
            }

            // Update because if a mac address is moved to another port / switch it will be updated
            Mac::updateOrCreate(
                [
                    'mac_address' => $mac['mac'],
                    'type' => 'client'
                ],
                [
                    'device_id' => $device->id,
                    'port_id' => $mac['port'],
                    'vlan_id' => $mac['vlan'],
                ]
            );

            // Store mac address with ip address
            if (isset($mac['ip'])) {
                SnmpMacData::updateOrCreate(
                    [
                        'mac_address' => $mac['mac'],
                    ],
                    [
                        'mac_address' => $mac['mac'],
                        'ip_address' => $mac['ip'],
                        'router' => $device->hostname,
                    ]
                );
            }
        }
    }

    static function updateDeviceSystemInfo($system, $device)
    {
        // Prevent overwriting device data with empty data
        $device->named = $system['name'] ?? $device->named;
        $device->model = $system['model'] ?? $device->model;
        $device->serial = $system['serial'] ?? $device->serial;
        $device->hardware = $system['hardware'] ?? $device->hardware;
        $device->mac_address = $system['mac'] ?? $device->mac_address;
        $device->firmware = $system['firmware'] ?? $device->firmware;

        if (isset($system['uptime']) && $system['uptime'] != "") {
            $device->uptime = $system['uptime'];
        }
    }

    static function checkForUplinks($device, $found_uplinks)
    {
        foreach ($found_uplinks as $uplink) {
            if (str_contains($uplink, "Trk") || str_contains($uplink, "trk") || str_contains($uplink, "Trunk") || str_contains($uplink, "trunk") || str_contains($uplink, "port-channel")) {
                $port = DevicePort::where('device_id', $device->id)->where('name', $uplink)->first();
                if (!$port) {
                    $port = DevicePort::where('device_id', $device->id)->where('name', "1/1/" . $uplink)->first();
                }
                if (!$port) {
                    continue;
                }
                DeviceUplink::updateOrCreate(
                    [
                        'device_id' => $device->id,
                        'name' => $uplink,
                    ],
                    [
                        'device_port_id' => $port->id,
                    ]
                );
            }
        }

        $currentUplinks = $device->uplinks()->get('name')->keyBy('name')->toArray();
        // Client based uplink detection
        $clients = $device->clients()->get()->groupBy('port_id')->toArray();
        foreach ($clients as $port => $client) {
            if (count($client) > 10 && !isset($currentUplinks[$port]) && !str_contains($port, "port-channel")) {
                Notification::uplink($port, $device, [
                    'clients' => count($client),
                    'port' => $port,
                    'device_id' => $device->id,
                    'site_id' => $device->site_id,
                ], 'uplink', 'clients');
            }
        }

        // Vlan based uplink detection
        $vlanports = $device->vlanports()->get()->groupBy('device_port_id')->toArray();
        $vlans = $device->vlans()->get()->toArray();
        foreach ($vlanports as $portId => $vlanport) {
            $cur_port = DevicePort::where('id', $portId)->first();
            $port = $cur_port->name;
            if (count($vlanport) > (count($vlans) * 0.65) && !isset($currentUplinks[$port]) && !str_contains($port, "port-channel")) {
                Notification::uplink($port, $device, [
                    'vlans' => count($cur_port->tagged),
                    'port' => $port,
                    'device_id' => $device->id,
                    'site_id' => $device->site_id,
                ], 'uplink', 'vlans');
            }
        }

        // Topology based uplink detection
        $topology = $device->topology()->get(['local_port', 'remote_port', 'local_device', 'remote_device'])->toArray();
        foreach ($topology as $index => $port_combination) {
            if ($port_combination['local_port'] > $port_combination['remote_port']) {
                $temp = $port_combination['local_port'];
                $temp2 = $port_combination['local_device'];
                $topology[$index]['local_port'] = $port_combination['remote_port'];
                $topology[$index]['local_device'] = $port_combination['remote_device'];
                $topology[$index]['remote_port'] = $temp;
                $topology[$index]['remote_device'] = $temp2;
            }
        }

        $topology = array_unique($topology, SORT_REGULAR);

        foreach ($topology as $port_combination) {
            $local_port = str_replace(["ethernet"], "", $port_combination['local_port']);
            $remote_port = str_replace(["ethernet"], "", $port_combination['remote_port']);

            if ($port_combination['local_device'] == $device->id && !isset($currentUplinks[$local_port]) && !str_contains($port, "port-channel")) {
                $uplink = DevicePort::where('device_id', $port_combination['local_device'])->where('name', $local_port)->first();
                // if (!$uplink) {
                //     $uplink = DevicePort::where('device_id', $port_combination['local_device'])->where('name', "1/1/" . $local_port)->first();
                // }

                if (!$uplink) {
                    continue;
                }
            } elseif ($port_combination['remote_device'] == $device->id && !isset($currentUplinks[$remote_port]) && !isset($currentUplinks["1/1/" . $remote_port])) {
                $uplink = DevicePort::where('device_id', $port_combination['remote_device'])->where('name', $remote_port)->first();
                // if (!$uplink) {
                //     $uplink = DevicePort::where('device_id', $port_combination['remote_device'])->where('name', "1/1/" . $remote_port)->first();
                // }

                if (!$uplink) {
                    continue;
                }
            } else {
                continue;
            }

            echo "uplink - " . $uplink->name . "\n";
            Notification::uplink($uplink->name, $device, [
                'topology' => "Entry in topology detected",
                'port' => $uplink->name,
                'device_id' => $device->id,
                'site_id' => $device->site_id,
            ], 'uplink', 'topology');
        }
    }
}
