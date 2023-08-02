<?php

namespace App\Devices;

use App\Interfaces\DeviceInterface;

use App\Models\Device;

class DellEMC implements DeviceInterface
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

        try {
            $snmpIpToMac = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['ip_to_mac'], 5000000, 1);
        } catch (\Exception $e) {
            $snmpIpToMac = [];
        }

        $snmpPortsAssignedToVlans = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['assigned_ports_to_vlan'], 5000000, 1);
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
            if ((str_contains($value, 'vlan') || str_contains($value, 'VLAN') || str_contains($value, 'Vl')) && !str_contains($value, 'DEFAULT')) {
                $value = str_replace(["STRING: ", "\"", "vlan", "VLAN", "Vl"], "", $value);
                $allVlans[$value]['id'] = $ifIndex;
                $allVlansByIndex[$ifIndex] = $value;
            }

            if (str_contains($value, 'ethernet') && !str_contains($value, ':1')) {
                $value = str_replace(["STRING: ", "\"", "ethernet"], "", $value);
                $allPorts[$ifIndex] = ['name' => $value, 'type' => 'ethernet'];
            }

            if (str_contains($value, 'ethernet') && str_contains($value, ':1')) {
                $value = str_replace(["STRING: ", "\"", "ethernet"], "", $value);
                $portchannel = explode("/", $value);
                $portchannel = explode(":", $portchannel[2])[0];
                $allPorts[$ifIndex] = ['name' => $value, 'type' => 'port-channel' . $portchannel, 'tagged' => []];
            }
        }
        $allVlans = self::foreachAssignedVlansToPort($snmpPortsAssignedToVlans, $allVlans);
        
        list($allPorts, $allVlans) = self::foreachIfNames($snmpIfNames, $allPorts, $allVlans, $allVlansByIndex);
        $allPorts = self::foreachSetVlansToPorts($allVlans, $allPorts, $portExtendedIndex);
        $allPorts = self::foreachIfHighspeeds($snmpIfHighSpeed, $allPorts);
        $allPorts = self::foreachIfOperStatus($snmpIfOperStatus, $allPorts);

        $data = [
            'ports' => self::snmpFormatPortData($allPorts, []),
            'vlans' => self::snmpFormatVlanData($allVlans),
            'macs' => self::snmpFormatMacTableData($snmpIpToMac, $allVlansByIndex, $device, "", ""),
            'vlanports' => self::snmpFormatPortVlanData([$allPorts, $allVlans]),
            'informations' => self::snmpFormatSystemData(['data' => $snmpSysDescr, 'hostname' => $snmpHostname, 'uptime' => $snmpSysUptime]),
            'statistics' => self::snmpFormatExtendedPortStatisticData([], $allPorts),
            'uplinks' => self::snmpFormatUplinkData(['ports' => $allPorts, 'vlans' => $allVlans]),
            'success' => true,
        ];

        return $data;
    }

    static function snmpFormatPortData(array $ports, array $stats): array
    {
        $return = [];

        if (empty($ports) or !is_array($ports) or !isset($ports)) {
            return $return;
        }

        foreach ($ports as $ifIndex => $port) {
            $return[$port['name']] = [
                'name' => $port['description'],
                'id' => $port['name'],
                'link' => $port['status'] == "up" ? true : false,
                'trunk_group' => $port['trunk_group'] ?? null,
                'vlan_mode' => "native-untagged",
                'speed' => $port['speed'] ?? null,
                'snmp_if_index' => $ifIndex,
            ];
        }

        return $return;
    }

    static function snmpFormatPortVlanData(array $vlanports): array
    {
        $return = [];

        if (empty($vlanports) or !is_array($vlanports) or !isset($vlanports)) {
            return $return;
        }


        $i = 0;
        foreach ($vlanports[0] as $key => $port) {
            if (!isset($port['tagged'])) {
                continue;
            }

            foreach ($port['tagged'] as $vlan) {
                $return[$i] = [
                    "port_id" => $port['name'],
                    "vlan_id" => $vlan,
                    "is_tagged" => true,
                ];
                $i++;
            }
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

        if (str_contains($system['data'], "Dell Networking")) {
            $sys_data = str_replace("STRING: ", "", $system['data']);
            $sys_data = str_replace(["\"", "\r"], "", $sys_data);
            $sys_data = explode(", ", $sys_data);
            $model = $sys_data[0];
            $version = $sys_data[1];
        } else {
            $sys_data = str_replace("STRING: ", "", $system['data']);
            $sys_data = str_replace(["\"", "\r"], "", $sys_data);
            $sys_data = explode("\n", $sys_data);
            $model = str_replace("System Type: ", "", $sys_data[4]);
            $version = str_replace("OS Version: ", "", $sys_data[3]);
        }

        $uptime = str_replace("Timeticks: (", "", $system['uptime']);
        $uptime = strstr($uptime, ")", true);
        $uptime = ($uptime / 100) * 1000;

        $return = [
            'name' => $hostname,
            'model' => $model,
            'serial' => $system['serial_number'] ?? null,
            'firmware' => $version,
            'hardware' => $system['hardware'] ?? null,
            'mac' => null,
            'uptime' => $uptime ?? null,
        ];

        return $return;
    }
}
