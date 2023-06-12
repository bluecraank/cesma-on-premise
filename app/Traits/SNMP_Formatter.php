<?php
    namespace App\Traits;

    trait SNMP_Formatter
    {
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
    }


?>