<?php

namespace App\Services;

use App\Models\DevicePort;
use App\Models\DevicePortStat;
use App\Models\DeviceUplink;
use App\Models\DeviceVlan;
use App\Models\DeviceVlanPort;
use App\Models\Mac;
use App\Models\Notification;
use App\Models\SnmpMacData;

class UpdateDeviceData
{
    static function updateDevicePorts($ports, $device) {
        $existingPorts = $device->ports()->get('name')->keyBy('name')->toArray();
        
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
            $existingPorts[$port['id']] = true;
        }

        // Delete ports that are not in the list
        foreach($existingPorts as $port => $isExisting) {
            if(is_bool($isExisting) and $isExisting) {
                continue;
            }

            DevicePort::where('device_id', $device->id)->where('name', $port)->delete();
        }
    }

    static function updateDeviceVlans($vlans, $device) {
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
            foreach ($vlans as $vid => $vname) {
                $query->where('vlan_id', '!=', $vid);
            }
        })->delete();
    }

    static function updateVlanPorts($vlanports, $device) {
        $return = ['uplinks' => []];

        $currentVlanPorts = DeviceVlanPort::where('device_id', $device->id)->count();

        // Count new vlan ports
        $newVlanPorts = 0;
        foreach ($vlanports as $vlanport) {
            if ($vlanport['vlan_id'] != "Trunk" && $vlanport['vlan_id'] != "Trk") {
                $newVlanPorts++;
            }
        }

        // Update device last update time if vlan ports changed
        if ($currentVlanPorts != $newVlanPorts) {
            DeviceVlanPort::where('device_id', $device->id)->delete();
            $device->touch();
        }

        // Update/Create vlan ports and find uplinks
        foreach ($vlanports as $vlanport) {
            if ($vlanport['vlan_id'] == "Trunk") {
                $return['uplinks'][$vlanport['port_id']] = $vlanport['port_id'];
            } else {
                $device->vlanports()->updateOrCreate(
                    [
                        'device_port_id' => $device->ports()->where('name', $vlanport['port_id'])->first()->id,
                        'device_id' => $device->id,
                        'device_vlan_id' => $device->vlans()->where('vlan_id', $vlanport['vlan_id'])->first()->id,
                        'is_tagged' => $vlanport['is_tagged']
                    ]
                );
            }
        }

        return $return;
    }

    static function updateDeviceUplinks($uplinks, $new_uplinks, $device) {
        // If uplinks not match, delete all and update time
        // if (count($uplinks) != $device->uplinks()->count()) {
        //     $device->touch();
        //     $device->uplinks()->delete();
        // }

        // Update/Create uplinks
        // foreach ($uplinks as $port => $uplink) {
        //     DeviceUplink::updateOrCreate([
        //         'name' => $uplink,
        //         'device_id' => $device->id,
        //         'device_port_id' => $device->ports()->where('name', $port)->first()->id,
        //     ]);
        // }

        $uplinks = $device->uplinks()->get()->pluck('name')->toArray();

        return $uplinks;
    }

    static function updateDevicePortStatistics($statistics, $device) {
        foreach ($statistics as $statistic) {
            // Skip if no id is set
            if (!isset($statistic['id']) or !$statistic['id']) {
                continue;
            }

            // Get port id
            $id = $device->ports()->where('name', $statistic['id'])->first() ?? null;
            $id = $id->id ?? null;

            // Create port statistic
            if ($id) {
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

    static function updateMacData($macs, $combined_uplinks, $device) {
        foreach ($macs as $mac) {
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
                    'type' => NULL
                ],
                [
                    'device_id' => $device->id,
                    'port_id' => $mac['port'],
                    'vlan_id' => $mac['vlan'],
                ]
                );

            // Store mac address with ip address
            if(isset($mac['ip'])) {
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

    static function updateDeviceSystemInfo($system, $device) {
        // Prevent overwriting device data with empty data
        $device->named = $system['name'] ?? $device->named;
        $device->model = $system['model'] ?? $device->model;
        $device->serial = $system['serial'] ?? $device->serial;
        $device->hardware = $system['hardware'] ?? $device->hardware;
        $device->mac_address = $system['mac'] ?? $device->mac_address;
        $device->firmware = $system['firmware'] ?? $device->firmware;

        if(isset($system['uptime']) && $system['uptime'] != "") {
            $device->uptime = $system['uptime'];
        }
    }

    static function checkForUplinks($device) {
        $currentUplinks = $device->uplinks()->get()->pluck('name')->toArray();

        // Client based uplink detection
        $clients = $device->clients()->get()->groupBy('port_id')->toArray();
        foreach ($clients as $port => $client) {
            if (count($client) > 10 && !isset($currentUplinks[$port])) {
                // echo "Uplink detected on port " . $port . " on " . $device->hostname . "\n";
                Notification::updateOrCreate([
                    'unique-identifier' => 'uplink-' . $device->id . '-' . $port
                ],
                [
                    'title' => 'Uplink detected',
                    'message' => 'Port ' . $port . ' on ' . $device->hostname . ' has ' . count($client) . ' clients. Add this port as  uplink?',
                    'data' => json_encode([
                        'device_id' => $device->id,
                        'port' => $port,
                        'clients' => count($client)
                    ]),
                    'type' => 'uplink'
                ]);
            }
        }

        // Vlan based uplink detection
        $vlanports = $device->vlanports()->get()->groupBy('device_port_id')->toArray();
        $vlans = $device->vlans()->get()->toArray();
        foreach ($vlanports as $portId => $vlanport) {
            $port = DevicePort::where('id', $portId)->first()->name;
            if (count($vlanport) > (count($vlans)*0.8) && !isset($currentUplinks[$port])) {
                // echo "Uplink detected on port " . $port . " on " . $device->hostname . "\n";
                Notification::updateOrCreate([
                    'unique-identifier' => 'uplink-' . $device->id . '-' . $port
                ],
                [
                    'title' => 'Uplink detected',
                    'message' => 'Port ' . $port . ' on ' . $device->hostname . ' has ' . count($vlanport) . ' vlans. Add this port as  uplink?',
                    'data' => json_encode([
                        'device_id' => $device->id,
                        'port' => $port,
                        'vlans' => count($vlanport)
                    ]),
                    'type' => 'uplink'
                ]);
            }
        }

        // Topology based uplink detection
        $topology = $device->topology()->get(['local_port', 'remote_port', 'local_device', 'remote_device'])->toArray();
        foreach($topology as $index => $port_combination) {
            if($port_combination['local_port'] > $port_combination['remote_port']) {
                $temp = $port_combination['local_port'];
                $temp2 = $port_combination['local_device'];
                $topology[$index]['local_port'] = $port_combination['remote_port'];
                $topology[$index]['local_device'] = $port_combination['remote_device'];
                $topology[$index]['remote_port'] = $temp;
                $topology[$index]['remote_device'] = $temp2;
            }
        }

        $topology = array_unique($topology, SORT_REGULAR);

        foreach($topology as $port_combination) {
            $local_port = str_replace(["ethernet","1/1/"], "", $port_combination['local_port']);
            $remote_port = str_replace(["ethernet","1/1/"], "", $port_combination['remote_port']);

            if($port_combination['local_device'] == $device->id && !isset($currentUplinks[$local_port])) {
                $uplink = DevicePort::where('name', $local_port)->first();  
                if(!$uplink) {
                    $uplink = DevicePort::where('name', "1/1/".$local_port)->first();
                } 
            } elseif($port_combination['remote_device'] == $device->id && !isset($currentUplinks[$remote_port])) {
                $uplink = DevicePort::where('name', $remote_port)->first();
                if(!$uplink) {
                    $uplink = DevicePort::where('name', "1/1/".$remote_port)->first();
                }
            } else {
                continue;
            }

            // dd($uplink);
            // echo "Uplink detected on port " . $uplink->name . " on " . $device->hostname . "\n";
            Notification::updateOrCreate([
                'unique-identifier' => 'uplink-' . $device->id . '-' . $uplink->name
            ],
            [
                'title' => 'Uplink detected',
                'message' => 'Port ' . $uplink->name . ' on ' . $device->hostname . ' has a topology entry. Add this port as  uplink?',
                'data' => json_encode([
                    'device_id' => $device->id,
                    'port' => $uplink->name,
                ]),
                'type' => 'uplink'
            ]);
        }
    }

}