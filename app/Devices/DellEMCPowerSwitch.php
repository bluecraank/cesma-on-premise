<?php

namespace App\Devices;

use App\Interfaces\DeviceInterface;

use App\Models\Device;

class DellEMCPowerSwitch implements DeviceInterface
{
    use \App\Traits\DefaultSnmpMethods;
    use \App\Traits\DefaultApiMethods;
    use \App\Traits\DefaultDevice;

    static $fetch_from = [
        'snmp' => true,
        'api' => false,
    ];

    static function getSnmpData(Device $device): array
    {
        $snmpIfNames = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['if_name'], 5000000, 1);
        $snmpIfIndexes = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['if_index'], 5000000, 1);

        $snmpIpToMac = [];
        $snmpMacToPort = [];
        $snmpPortsAssignedToVlans = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['assigned_ports_to_vlan'], 5000000, 1);
        $snmpPortsAssignedToUntaggedVlan = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['untagged_ports'], 5000000, 1);
        $snmpPortIndexToQBridgeIndex = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['if_index_to_port'], 5000000, 1);
        $snmpIfOperStatus = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['ifOperStatus'], 5000000, 1);
        $snmpIfHighSpeed = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['ifHighSpeed'], 5000000, 1);
        $snmpSysDescr = snmp2_get($device->hostname, 'public', self::$snmp_oids['sysDescr'], 5000000, 1);
        $snmpSysUptime = snmp2_get($device->hostname, 'public', self::$snmp_oids['sysUptime'], 5000000, 1);
        $snmpHostname = snmp2_get($device->hostname, 'public', self::$snmp_oids['hostname'], 5000000, 1);

        $allVlans = [];
        $allPorts = [];
        $portExtendedIndex = [];
        $allVlansByIndex = [];

        if (is_object($snmpHostname) || !is_array($snmpIfNames) || !is_array($snmpIfIndexes) || !is_array($snmpIpToMac) || !is_array($snmpPortsAssignedToVlans) || !is_array($snmpPortIndexToQBridgeIndex)) {
            return ['message' => 'Failed to get data from device', 'success' => false];
        }

        foreach ($snmpPortIndexToQBridgeIndex as $key => $value) {
            $key = explode(".", $key);
            $ifIndex = $key[count($key) - 1];
            $value = str_replace("INTEGER: ", "", $value);
            $portExtendedIndex[$ifIndex] = $value;
        }

        foreach ($snmpIfIndexes as $key => $value) {
            $key = explode(".", $key);
            $ifIndex = $key[count($key) - 1];
            if (str_contains($value, 'Vl') && !str_contains($value, 'DEFAULT')) {
                $value = str_replace(["STRING: ", "\"", "vlan", "VLAN", "Vl"], "", $value);
                $allVlans[$value]['id'] = $ifIndex;
                $allVlansByIndex[$ifIndex] = $value;
            }

            if (str_contains($value, 'Unit: 1 Slot: 0')) {
                $value = str_replace(["STRING: ", "\""], "", $value);
                $port_id = explode(" ", $value);
                $combined_port = $port_id[1] . "/" . $port_id[3] . "/" . $port_id[5];
                $allPorts[$ifIndex] = ['name' => $combined_port, 'type' => 'ethernet'];
            }
        }
        $allVlans = self::foreachAssignedUntaggedVlansToPort($snmpPortsAssignedToUntaggedVlan, $allVlans);
        $allVlans = self::foreachAssignedVlansToPort($snmpPortsAssignedToVlans, $allVlans);

        list($allPorts, $allVlans) = self::foreachIfNames($snmpIfNames, $allPorts, $allVlans, $allVlansByIndex);
        $allPorts = self::foreachSetVlansToPorts($allVlans, $allPorts, $portExtendedIndex);
        $allPorts = self::foreachIfHighspeeds($snmpIfHighSpeed, $allPorts);
        $allPorts = self::foreachIfOperStatus($snmpIfOperStatus, $allPorts);

        $data = [
            'ports' => self::snmpFormatPortData($allPorts, []),
            'vlans' => self::snmpFormatVlanData($allVlans),
            'macs' => self::snmpFormatMacTableData($snmpIpToMac, $allVlansByIndex, $snmpMacToPort, $device),
            'vlanports' => self::snmpFormatPortVlanData([$allPorts, $allVlans]),
            'informations' => self::snmpFormatSystemData(['data' => $snmpSysDescr, 'hostname' => $snmpHostname, 'uptime' => $snmpSysUptime]),
            'statistics' => self::snmpFormatExtendedPortStatisticData([], $allPorts),
            'uplinks' => self::snmpFormatUplinkData(['ports' => $allPorts, 'vlans' => $allVlans]),
            'success' => true,
        ];

        return $data;
    }

    static function snmpFormatPortVlanData(array $vlanports): array
    {
        $return = [];

        if (empty($vlanports) or !is_array($vlanports) or !isset($vlanports)) {
            return $return;
        }


        $i = 0;

        $cache = [];

        foreach ($vlanports[0] as $key => $port) {

            if (isset($port['untagged'])) {
                foreach ($port['untagged'] as $vlan) {
                    $return[$i] = [
                        "port_id" => $port['name'],
                        "vlan_id" => $vlan,
                        "is_tagged" => false,
                    ];
                    $i++;
                    $cache[$port['name']] = $vlan;
                }
            }

            if (isset($port['tagged'])) {
                foreach ($port['tagged'] as $vlan) {
                    if (isset($cache[$port['name']]) && $cache[$port['name']] == $vlan) {
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

    static function snmpFormatUplinkData($data): array
    {
        $uplinks = [];

        foreach ($data['ports'] as $port) {
            if (isset($port['tagged']) && count($port['tagged']) >= count($data['vlans']) - 10 && count($data['vlans']) > 15) {
                $uplinks[$port['name']] = $port['name'];
            }
        }

        return $uplinks;
    }


    static function snmpFormatVlanData(array $vlans): array
    {
        $return = [];

        if (empty($vlans) or !is_array($vlans) or !isset($vlans)) {
            return $return;
        }

        foreach ($vlans as $key => $vlan) {
            $return[$key] = $vlan['description'] != "" ? $vlan['description'] : "VLAN " . $key;
        }

        return $return;
    }

    static function snmpFormatMacTableData(array $data, array $vlans, array $macData, Device $device): array
    {
        $return = [];

        if (empty($data) or !is_array($data) or !isset($data)) {
            return $return;
        }

        foreach ($data as $ip => $mac) {
            $exploded_ip = explode(".", $ip);
            $formatted_ip = array_slice($exploded_ip, -4, 4, true);

            $vlan = array_slice($exploded_ip, -5, 1, true);

            $formatted_ip = implode(".", $formatted_ip);

            $formatted_mac = str_replace(["Hex-STRING: ", " "], "", $mac);

            $return[$formatted_mac] = [
                'port' => 9999,
                'mac' => $formatted_mac,
                'vlan' => $vlans[$vlan[10]],
                'ip' => $formatted_ip,
            ];
        }

        return $return;
    }

    static function snmpFormatSystemData(array $system): array
    {
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

    static function formatPortData(array $ports, array $stats): array
    {
        $return = [];
        return $return;
    }

    static function setUntaggedVlanToPort($newVlan, $port, $device, $vlans, $need_login = true, $logindata = ""): bool
    {
        $oid = self::$snmp_oids['untagged_ports'] . "." . ($port->snmp_if_index ?? '0');

        $ports = $device->ports()->get()->toArray();


        $portHexString = [];
        foreach ($ports as $key => $untaggedPort) {
            if ((isset($untaggedPort['untagged']) and $untaggedPort['untagged']['id'] == $newVlan) || $port->id == $untaggedPort['id']) {
                $portHexString[$key] = "1";
            } else {
                $portHexString[$key] = "0";
            }
        }

        return true;
    }
}
