<?php
    namespace App\Traits;

    use App\Models\Device;

    trait DefaultSnmpMethods
    {
        static $snmp_oids = [
            'hostname' => '.1.3.6.1.2.1.1.5.0',
            'if_name' => '.1.3.6.1.2.1.31.1.1.1.18',
            'if_index' => '.1.3.6.1.2.1.2.2.1.2',
            'if_types' => '.1.3.6.1.2.1.2.2.1.3',
            'if_index_to_port' => '.1.3.6.1.2.1.17.1.4.1.2',
            'ip_to_mac' => '1.3.6.1.2.1.4.22.1.2',
            'assigned_ports_to_vlan' => '.1.3.6.1.2.1.17.7.1.4.3.1.2',
            'untagged_ports' => '.1.3.6.1.2.1.17.7.1.4.2.1.5.0',
            'ifOperStatus' => '.1.3.6.1.2.1.2.2.1.8',
            'ifHighSpeed' => '1.3.6.1.2.1.31.1.1.1.15',
            'sysDescr' => '.1.3.6.1.2.1.1.1.0',
            'vlan_to_mac' => '.1.3.6.1.2.1.17.4.3.1.1',
            'macToIf' => '.1.3.6.1.2.1.17.4.3.1.2',
            'sysUptime' => '.1.3.6.1.2.1.1.3.0',
            'macToPort' => '.1.3.6.1.2.1.17.7.1.2.2.1.2'
        ];

        static function getSnmpData(Device $device): array
        {
            return [];
        }

        protected static function foreachAssignedVlansToPort($data, $vlan_array) : array {
            foreach($data as $key => $value) {
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
                $allVlans[$vlan_id] = array_merge($vlan_array[$vlan_id], ['ports' => $ports]);
            }

            return $allVlans;
        }

        protected static function foreachAssignedUntaggedVlansToPort($data, $vlan_array) {
            foreach($data as $key => $value) {
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
                $allVlans[$vlan_id] = array_merge($vlan_array[$vlan_id], ['untagged_ports' => $ports]);
            }

            return $allVlans;
        }

        protected static function foreachIfNames($data, $allPorts, $allVlans, $allVlansByIndex) {
            foreach($data as $key => $value) {
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

            return [$allPorts, $allVlans];
        }

        protected static function foreachSetVlansToPorts($allVlans, $allPorts, $portExtendedIndex) {
            foreach($allVlans as $vlan_id => $value) {
                $portsAssigned = $value['ports'];
                foreach($portsAssigned as $key => $port) {
                    if($port == 1) {
                        if(isset($portExtendedIndex[$key+1]) && isset($allPorts[$portExtendedIndex[$key+1]])) {
                            $allPorts[$portExtendedIndex[$key+1]]['tagged'][] = $vlan_id;
                        }
                    }
                }

                if(isset($value['untagged_ports'])) {
                    $untaggedPortsAssigned = $value['untagged_ports'];
                    foreach($untaggedPortsAssigned as $key => $port) {
                        if($port == 1) {
                            if(isset($portExtendedIndex[$key+1]) && isset($allPorts[$portExtendedIndex[$key+1]])) {
                                $allPorts[$portExtendedIndex[$key+1]]['untagged'][] = $vlan_id;
                            }
                        }
                    }
                }
            }

            return $allPorts;
        }

        protected static function foreachIfHighspeeds($data, $allPorts) {
            foreach($data as $key => $value) {
                $key = explode(".", $key);
                $ifIndex = $key[count($key) - 1];
               $value = str_replace("Gauge32: ", "", $value);
                if(isset($allPorts[$ifIndex])) {
                    $allPorts[$ifIndex] = array_merge($allPorts[$ifIndex], ['speed' => intval($value)]);
                }
            }

            return $allPorts;
        }

        protected static function foreachIfOperStatus($data, $allPorts) {
            foreach($data as $key => $value) {
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

            return $allPorts;
        }

        static function snmpFormatPortVlanData(array $vlanports): array
        {
            return [];
        }

        static function snmpFormatSystemData(array $system): array
        {
            return [];
        }

        static function snmpFormatExtendedPortStatisticData(array $portstats, array $portdata): array
        {
            // Incompatible with DellEMC
            return [];
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
                if ($vlan['description'] != "") {
                    $return[$key] = $vlan['description'];
                }
            }

            return $return;
        }

        static function snmpFormatMacTableData(array $data, array $vlans, array $snmpMacToPort, Device $device): array
        {
            $return = [];

            // if (empty($data) or !is_array($data) or !isset($data)) {
            //     return $return;
            // }

            // $ports = DevicePort::where('device_id', $device->id)->get()->keyBy('snmp_if_index')->toArray();

            // $mac_port = [];
            // foreach($snmpMacToPort as $mac => $port) {
            //     $mac = explode(".", $mac);

            //     $decimal_mac = [
            //         dechex(intval($mac[count($mac) - 6])),
            //         dechex(intval($mac[count($mac) - 5])),
            //         dechex(intval($mac[count($mac) - 4])),
            //         dechex(intval($mac[count($mac) - 3])),
            //         dechex(intval($mac[count($mac) - 2])),
            //         dechex(intval($mac[count($mac) - 1])),
            //     ];

            //     foreach($decimal_mac as $key => $value) {

            //         if(strlen($value) == 1) {
            //             $value = "0" . $value;
            //         }

            //         $decimal_mac[$key] = $value;
            //     }

            //     $port = str_replace("INTEGER: ", "", $port);

            //     $mac = implode("", $decimal_mac);
            //     $mac_port[$mac] = $port;
            // }

            // foreach ($data as $oid => $mac) {
            //     $exploded_oid = explode(".", $oid);
            //     $port_id = $exploded_oid[11];
            //     $formatted_mac = str_replace(["Hex-STRING: ", " "], "", $mac);

            //     if($port == 0) {
            //         continue;
            //     }

            //     if (isset($ports[$port_id])) {
            //         $return[$formatted_mac] = [
            //             'port' => $ports[$port_id]['name'],
            //             'mac' => $formatted_mac,
            //             'vlan' => 0,
            //         ];
            //     }
            // }

            return $return;
        }

        static function snmpFormatIpMacTableData(array $data, array $vlans, Device $device): array
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

                if (isset($vlans[$vlan[10]])) {
                    $return[$formatted_mac] = [
                        'port' => 0,
                        'mac' => $formatted_mac,
                        'vlan' => $vlans[$vlan[10]],
                        'ip' => $formatted_ip,
                    ];
                }
            }

            return $return;
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
    }
