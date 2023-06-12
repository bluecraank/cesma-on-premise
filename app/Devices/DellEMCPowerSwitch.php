<?php

namespace App\Devices;

use App\Interfaces\DeviceInterface;

use App\Models\Device;
use App\Models\DeviceBackup;
use App\Models\Vlan;

class DellEMCPowerSwitch implements DeviceInterface {

    static $snmp_oids = [
        'hostname' => '.1.3.6.1.2.1.1.5.0',
        'if_name' => '.1.3.6.1.2.1.31.1.1.1.18',
        'if_index' => '.1.3.6.1.2.1.2.2.1.2',
        'if_index_to_port' => '.1.3.6.1.2.1.17.1.4.1.2',
        'ip_to_mac' => '1.3.6.1.2.1.4.22.1.2',
        'assigned_ports_to_vlan' => '.1.3.6.1.2.1.17.7.1.4.3.1.2',
        'untagged_ports' => '.1.3.6.1.2.1.17.7.1.4.2.1.5.0',
        'ifOperStatus' => '.1.3.6.1.2.1.2.2.1.8',
        'ifHighSpeed' => '1.3.6.1.2.1.31.1.1.1.15',
        'sysDescr' => '.1.3.6.1.2.1.1.1.0',
        'macToPort' => '.1.3.6.1.2.1.17.4.3.1.1',
        'macToIf' => '.1.3.6.1.2.1.17.4.3.1.2'
    ];

    static function getSnmpData(Device $device): array {    
        $snmpIfNames = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['if_name'], 5000000, 1);
        $snmpIfIndexes = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['if_index'], 5000000, 1);

        // try {
        //     $snmpIpToMac = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['ip_to_mac'], 5000000, 1);
        // } catch(\Exception $e) {
            $snmpIpToMac = [];
        // }

        $snmpPortsAssignedToVlans = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['assigned_ports_to_vlan'], 5000000, 1);
        $snmpPortsAssignedToUntaggedVlan = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['untagged_ports'], 5000000, 1);
        $snmpPortIndexToQBridgeIndex = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['if_index_to_port'], 5000000, 1);
        $snmpIfOperStatus = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['ifOperStatus'], 5000000, 1);
        $snmpIfHighSpeed = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['ifHighSpeed'], 5000000, 1);
        // $snmp_data9 = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['macToPort'], 5000000, 1);
        // $snmpIfNames0 = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['macToIf'], 5000000, 1);
        $snmpSysDescr = snmp2_get($device->hostname, 'public', self::$snmp_oids['sysDescr'], 5000000, 1);
        $snmpSysUptime = snmp2_get($device->hostname, 'public', '.1.3.6.1.2.1.1.3.0', 5000000, 1);
        $snmpHostname = snmp2_get($device->hostname, 'public', self::$snmp_oids['hostname'], 5000000, 1);

        $ports = [];
        $allVlans = [];
        $allPorts = [];
        $portExtendedIndex = [];
        $allVlansByIndex = [];

        if(is_object($snmpHostname) || !is_array($snmpIfNames) || !is_array($snmpIfIndexes) || !is_array($snmpIpToMac) || !is_array($snmpPortsAssignedToVlans) || !is_array($snmpPortIndexToQBridgeIndex)) {
            return ['message' => 'Failed to get data from device', 'success' => false];
        }


        foreach($snmpPortIndexToQBridgeIndex as $key => $value) {
            $key = explode(".", $key);
            $ifIndex = $key[count($key) - 1];
            $value = str_replace("INTEGER: ", "", $value);
            $portExtendedIndex[$ifIndex] = $value;
        }

        foreach($snmpIfIndexes as $key => $value) {
            $key = explode(".", $key);
            $ifIndex = $key[count($key) - 1];
            if(str_contains($value, 'Vl') && !str_contains($value, 'DEFAULT')) {
                $value = str_replace(["STRING: ","\"", "vlan", "VLAN", "Vl"], "", $value);
                $allVlans[$value]['id'] = $ifIndex;
                $allVlansByIndex[$ifIndex] = $value;
            }

            if(str_contains($value, 'Unit: 1 Slot: 0')) {
                $value = str_replace(["STRING: ","\""], "", $value);
                $port_id = explode(" ", $value);
                $combined_port = $port_id[1] . "/" . $port_id[3] . "/" . $port_id[5];

                $allPorts[$ifIndex] = ['name' => $combined_port, 'type' => 'ethernet'];
            }

            if(str_contains($value, 'ethernet') && str_contains($value, ':1')) {
                $value = str_replace(["STRING: ","\"", "ethernet"], "", $value);
                $portchannel = explode("/", $value);
                $portchannel = explode(":", $portchannel[2])[0];
                $allPorts[$ifIndex] = ['name' => $value, 'type' => 'port-channel'.$portchannel, 'tagged' => []];
            }

            // TODO: Rausfinden wie man genau port-channel berechnen kann

            // if(str_contains($value, 'port-channel')) {
            //     $value = str_replace(["STRING: ","\"", "port-channel"], "", $value);
            //     $allPorts[$ifIndex] = ['name' => $value, 'type' => 'port-channel', 'tagged' => []];
            // }
        }


        foreach($snmpPortsAssignedToVlans as $key => $value) {
            $vlan_id = explode(".", $key);
            $vlan_id = $vlan_id[count($vlan_id) - 1];
            $value = str_replace("Hex-STRING: ", "", $value);
            $value = explode(" ", $value);
            $i = 1;

            $ports = [];
            foreach($value as $port) {
                $port = hexdec($port);
                $port = sprintf( "%08d", decbin($port));
                $ports[$i] = $port;
                $i++;
            }
            $ports = implode("", $ports);
            $ports = str_split($ports);
            $allVlans[$vlan_id] = array_merge($allVlans[$vlan_id], ['ports' => $ports]);
        }

        foreach($snmpPortsAssignedToUntaggedVlan as $key => $value) {
            $vlan_id = explode(".", $key);
            $vlan_id = $vlan_id[count($vlan_id) - 1];
            $value = str_replace("Hex-STRING: ", "", $value);
            $value = explode(" ", $value);
            $i = 1;

            $ports = [];
            foreach($value as $port) {
                $port = hexdec($port);
                $port = sprintf( "%08d", decbin($port));
                $ports[$i] = $port;
                $i++;
            }
            $ports = implode("", $ports);
            $ports = str_split($ports);
            $allVlans[$vlan_id] = array_merge($allVlans[$vlan_id], ['untagged_ports' => $ports]);
        }

        foreach($snmpIfNames as $key => $value) {
            $key = explode(".", $key);
            $ifIndex = $key[count($key) - 1];

            $description = str_replace(["STRING: ","\""], "", $value);
            if(isset($allPorts[$ifIndex])) {
                $allPorts[$ifIndex] = array_merge($allPorts[$ifIndex], ['description' => $description]);
            }

            if(isset($allVlansByIndex[$ifIndex]))
            {
                $allVlans[$allVlansByIndex[$ifIndex]] = array_merge($allVlans[$allVlansByIndex[$ifIndex]], ['description' => $description]);
            }
        }

        foreach($allVlans as $vlan_id => $value) {
            $portsAssigned = $value['ports'];
            foreach($portsAssigned as $key => $port) {
                if($port == 1) {   
                    if(isset($portExtendedIndex[$key+1]) && isset($allPorts[$portExtendedIndex[$key+1]])) {
                        $allPorts[$portExtendedIndex[$key+1]]['tagged'][] = $vlan_id;
                    }
                }
            }

            $untaggedPortsAssigned = $value['untagged_ports'];
            foreach($untaggedPortsAssigned as $key => $port) {
                if($port == 1) {
                    if(isset($portExtendedIndex[$key+1]) && isset($allPorts[$portExtendedIndex[$key+1]])) {
                        $allPorts[$portExtendedIndex[$key+1]]['untagged'][] = $vlan_id;
                    }
                }
            }
        }

        foreach($snmpIfHighSpeed as $key => $value) {
            $key = explode(".", $key);
            $ifIndex = $key[count($key) - 1];
           $value = str_replace("Gauge32: ", "", $value);
            if(isset($allPorts[$ifIndex])) {
                $allPorts[$ifIndex] = array_merge($allPorts[$ifIndex], ['speed' => intval($value)]);
            }
        }

        foreach($snmpIfOperStatus as $key => $value) {
            $key = explode(".", $key);
            $ifIndex = $key[count($key) - 1];
            $value = str_replace("INTEGER: ", "", $value);
            if($value == 1) {
                $value = "up";
            } else {
                $value = "down";
            }

            if(isset($allPorts[$ifIndex])) {
                $allPorts[$ifIndex] = array_merge($allPorts[$ifIndex], ['status' => $value]);
            }
        }

        $data = [
            'ports' => self::formatPortData($allPorts, []),
            'vlans' => self::formatVlanData($allVlans),
            'macs' => self::formatMacTableData($snmpIpToMac, $allVlansByIndex, $device, "", ""),
            'vlanports' => self::formatPortVlanData([$allPorts, $allVlans]),
            'informations' => self::formatSystemData(['data' => $snmpSysDescr, 'hostname' => $snmpHostname, 'uptime' => $snmpSysUptime]),
            'statistics' => self::formatExtendedPortStatisticData([], $allPorts),
            'uplinks' => self::formatUplinkData(['ports' => $allPorts, 'vlans' => $allVlans]),
            'success' => true,
        ];
        
        return $data;
    }

    static function API_GET_VERSIONS(Device $device): string {
        // Incompatible with DellEMC
        return "";
    }

    static function API_LOGIN(Device $device): string {
        // Incompatible with DellEMC
        return "";
    }

    static function API_LOGOUT(String $hostname, String $cookie, String $api_version): bool {
        // Incompatible with DellEMC
        return false;
    }

    static function API_PUT_DATA(String $hostname, String $cookie, String $api, String $api_version, String $data): array {
        // Incompatible with DellEMC
        return [];
    }

    static function API_GET_DATA(String $hostname, String $cookie, String $api, String $api_version, Bool $plain): array {
        // Incompatible with DellEMC
        return [];
    }

    static function API_POST_DATA(String $hostname, String $cookie, String $api, String $api_version, String $data): array {
        // Incompatible with DellEMC
        return [];
    }

    static function API_DELETE_DATA(String $hostname, String $cookie, String $api, String $api_version, String $data): array {
        // Incompatible with DellEMC
        return [];
    }
    
    static function API_PATCH_DATA(String $hostname, String $cookie, String $api, String $api_version, String $data): array {
        // Incompatible with DellEMC
        return [];
    }

    static function API_REQUEST_ALL_DATA(Device $device): array {
        // Incompatible with DellEMC
        return self::getSnmpData($device);
    }

    static function createBackup(Device $device): bool {
        // Incompatible with Dell EMC
        return false;
    }

    static function restoreBackup(Device $device, DeviceBackup $backup, String $password): array {
        // Incompatible with DellEMC
        return [];
    }

    static function formatPortData(Array $ports, Array $stats): array {
        $return = [];

        if (empty($ports) or !is_array($ports) or !isset($ports)) {
            return $return;
        }

        foreach ($ports as $port) {
            $return[$port['name']] = [
                'name' => $port['description'],
                'id' => $port['name'],
                'link' => $port['status'] == "up" ? true : false,
                'trunk_group' => $port['trunk_group'] ?? null,
                'vlan_mode' => "native-untagged",
                'speed' => $port['speed'] ?? null,
            ];
        }

        return $return;
    }

    static function formatExtendedPortStatisticData(Array $portstats, Array $portdata): array {
        // Incompatible with DellEMC
        return [];
    }

    static function formatPortVlanData(Array $vlanports): array {
        $return = [];

        if (empty($vlanports) or !is_array($vlanports) or !isset($vlanports)) {
            return $return;
        }

        
        $i = 0;

        $cache = [];

        foreach ($vlanports[0] as $key => $port) {
                
            if(isset($port['untagged'])) {
                foreach($port['untagged'] as $vlan) {
                    $return[$i] = [
                        "port_id" => $port['name'],
                        "vlan_id" => $vlan,
                        "is_tagged" => false,
                    ];
                    $i++;
                    $cache[$port['name']] = $vlan;
                }
            }

            if(isset($port['tagged'])) {
                foreach($port['tagged'] as $vlan) {
                    if(isset($cache[$port['name']]) && $cache[$port['name']] == $vlan) {
                        continue;
                    }

                    $return[$i] = [
                        "port_id" => $port['name'],
                        "vlan_id" => $vlan,
                        "is_tagged" => true,
                    ];
                    $i++;
                }     
            }
        }

        return $return;
    }

    static function formatUplinkData($data): array
    {
        $uplinks = [];

        foreach ($data['ports'] as $port) {
            if (isset($port['tagged']) && count($port['tagged']) >= count($data['vlans'])-10) {
                $uplinks[$port['name']] = $port['name'];
            }
        }

        return $uplinks;
    }


    static function formatVlanData(Array $vlans): array {
        $return = [];

        if (empty($vlans) or !is_array($vlans) or !isset($vlans)) {
            return $return;
        }

        foreach ($vlans as $key => $vlan) {
            $return[$key] = $vlan['description'] != "" ? $vlan['description'] : "VLAN " . $key;
        }

        return $return;
    }

    static function formatMacTableData(Array $data, Array $vlans, Device $device, String $cookie, String $api_version): array {
        // Not supported by DellEMC
        $return = [];

        if (empty($data) or !is_array($data) or !isset($data)) {
            return $return;
        }

        foreach($data as $ip => $mac) {
            $exploded_ip = explode(".", $ip);
            $formatted_ip = array_slice($exploded_ip, -4, 4, true);

            $vlan = array_slice($exploded_ip, -5, 1, true);

            $formatted_ip = implode(".", $formatted_ip);

            $formatted_mac = str_replace(["Hex-STRING: ", " "], "", $mac);

            $return[$formatted_mac] = [
                'port' => 0,
                'mac' => $formatted_mac,
                'vlan' => $vlans[$vlan[10]],
                'ip' => $formatted_ip,
            ];
        }

        return $return;
    }
    
    static function formatSystemData(Array $system): array {
        $return = [];

        if (empty($system) or !is_array($system) or !isset($system)) {
            return [];
        }

        $hostname = str_replace("STRING: ", "", $system['hostname']);
        $hostname = str_replace("\"", "", $hostname);

        $sys_data = str_replace("STRING: ", "", $system['data']);
        $sys_data = str_replace(["\"", "\r"], "", $sys_data);
        $sys_data = explode(", ", $sys_data);
        $model = $sys_data[0];
        $version = $sys_data[1];

        $uptime = str_replace("Timeticks: (", "", $system['uptime']);
        $uptime = strstr($uptime, ")", true);
        $uptime = ($uptime / 100) * 1000;


        $return = [
            'name' => $hostname,
            'model' => $model,
            'serial' => $system['serial_number'] ?? 'unknown',
            'firmware' => $version,
            'hardware' => $system['hardware'] ?? 'unknown',
            'mac' => null,
            'uptime' => $uptime ?? 'unknown',
        ];

        return $return;
    }

    static function uploadPubkeys($device, $pubkeys): string {
        // Incompatible with DellEMC
        return "";
    }

    static function setUntaggedVlanToPort($vlan, $port, $device, $vlans, $need_login, $login_info): array {
        // Incompatible with DellEMC
        return [];   
    }

    static function setTaggedVlansToPort($taggedVlans, $port, $device, $vlans, $need_login, $login_info): array {
        // Incompatible with DellEMC
        return [];
    }

    static function syncVlans(Vlan $vlans, Array $vlans_of_switch, Device $device, Bool $create_vlans, Bool $overwrite_name,  Bool $test_mode): array {
        // Incompatible with DellEMC
        return [];
    }
}