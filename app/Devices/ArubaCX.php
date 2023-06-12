<?php

namespace App\Devices;

use App\Interfaces\DeviceInterface;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

use App\Helper\CLog;
use App\Models\DeviceVlanPort;
use App\Services\PublicKeyService;
use App\Http\Controllers\BackupController;
use App\Models\Device;

class ArubaCX implements DeviceInterface
{
    static $api_auth = [
        "login" => "login",
        "logout" => "logout",
    ];

    static $available_apis = [
        "status" => 'system?attributes=software_version,subsystems,hostname&depth=3',
        "subsystem" => 'system/subsystems/chassis,1?attributes=product_info',
        "vlans" => 'system/vlans?attributes=name,id&depth=2',
        "ports" => 'system/interfaces?attributes=ifindex,vlan_mode,link_state,description&depth=2',
        "portstats" => 'system/interfaces?attributes=ifindex,rate_statistics,statistics,link_speed,description&depth=2',
        "vlanport" => 'system/interfaces?attributes=ifindex,vlan_mode,vlan_tag,vlan_trunks&depth=2',
    ];

    static $port_if_uri = "system/interfaces/1%2F1%2F";

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

        try {
            $snmpIpToMac = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['ip_to_mac'], 5000000, 1);
        } catch(\Exception $e) {
            $snmpIpToMac = [];
        }
        
        $snmpPortsAssignedToVlans = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['assigned_ports_to_vlan'], 5000000, 1);
        $snmpPortsAssignedToUntaggedVlan = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['untagged_ports'], 5000000, 1);
        $snmpPortIndexToQBridgeIndex = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['if_index_to_port'], 5000000, 1);
        $snmpIfOperStatus = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['ifOperStatus'], 5000000, 1);
        $snmpIfHighSpeed = snmp2_real_walk($device->hostname, 'public', self::$snmp_oids['ifHighSpeed'], 5000000, 1);
        $snmpIfTypes = snmp2_real_walk($device->hostname, 'public', '.1.3.6.1.2.1.2.2.1.3', 5000000, 1);
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
        
        // dd($snmpHostname, $snmpIfHighSpeed, $snmpIfIndexes, $snmpIfNames, $snmpPortIndexToQBridgeIndex);
        if(is_object($snmpHostname) || !is_array($snmpIfNames) || !is_array($snmpIfIndexes) || !is_array($snmpIpToMac) || !is_array($snmpPortsAssignedToVlans) || !is_array($snmpPortIndexToQBridgeIndex)) {
            return ['message' => 'Failed to get data from device', 'success' => false];
        }


        foreach($snmpPortIndexToQBridgeIndex as $key => $value) {
            $key = explode(".", $key);
            $ifIndex = $key[count($key) - 1];
            $value = str_replace("INTEGER: ", "", $value);
            $portExtendedIndex[$ifIndex] = $value;
        }

        $types = [];
        foreach($snmpIfTypes as $key => $value) {
            $key = explode(".", $key);
            $ifIndex = $key[count($key) - 1];
            $value = str_replace("INTEGER: ", "", $value);
            $types[$ifIndex] = $value;
        }

        // dd($snmpIfIndexes);
        foreach($snmpIfIndexes as $key => $value) {

            $key = explode(".", $key);
            $ifIndex = $key[count($key) - 1];

            if(str_contains($value, 'vlan') || str_contains($value, 'VLAN') || str_contains($value, 'Vl') || str_contains($value, 'DEFAULT_VLAN')) {
                if(str_contains($value, 'DEFAULT_VLAN')) {
                    $value = "1";
                } else {
                    $value = str_replace(["STRING: ","\"", "vlan", "VLAN", "Vl"], "", $value);
                }

                $allVlans[$value]['id'] = $ifIndex;
                $allVlansByIndex[$ifIndex] = $value;
            }

            if(isset($types[$ifIndex]) && $types[$ifIndex] == 6) {
                $value = str_replace(["STRING: ","\"", "ethernet"], "", $value);
                $allPorts[$ifIndex] = ['name' => $value, 'type' => 'ethernet'];
            }
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

    static function API_GET_VERSIONS($hostname): string
    {
        $https = config('app.https');
        $url = $https . $hostname . '/rest';

        try {
            $versions = Http::withoutVerifying()->get($url);
            if ($versions->successful()) {
                $versionsFound = $versions->json()['latest'];
                return $versionsFound['version'];
            }
        } catch (\Exception $e) {
        }

        return "v10.11";
    }

    static function API_LOGIN($device): string
    {
        $api_version = self::API_GET_VERSIONS($device->hostname);

        $api_url = config('app.https') . $device->hostname . '/rest/' . $api_version . '/' . self::$api_auth['login'];

        $api_username = config('app.api_username');

        $api_password = Crypt::decrypt($device->password);

        try {
            $response = Http::connectTimeout(3)->withoutVerifying()->asForm()->post($api_url, [
                'username' => $api_username,
                'password' => $api_password,
            ]);

            // Return cookie if login was successful
            if ($response->successful() and !empty($response->header('Set-Cookie'))) {
                return $response->cookies()->toArray()[0]['Name'] . "=" . $response->cookies()->toArray()[0]['Value'] . ";" . $api_version;
            } else {
                Log::error("[Error] Failed to login to device " . $device->name . " ERROR: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("[Error] Failed to login to device " . $device->name . " ERROR: " . $e->getMessage());
            return "";
        }

        return "";
    }

    static function API_LOGOUT($hostname, $cookie, $api_version): bool
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $api_version . '/' . self::$api_auth['logout'];

        try {
            $logout = Http::connectTimeout(8)->withoutVerifying()->withHeaders([
                'Cookie' => "$cookie",
            ])->post($api_url);

            if ($logout->successful()) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    static function API_PUT_DATA($hostname, $cookie, $api, $version, $data): array
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' . $api;
        try {

            $response = Http::withBody($data, 'application/json')->withoutVerifying()->withHeaders([
                'Cookie' => "$cookie",
            ])->put($api_url);

            if ($response->successful()) {
                return ['success' => true, 'data' => array($response->status(), $response->json())];
            } else {
                return ['success' => false, 'data' => $response->json()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'data' => $e->getMessage()];
        }
    }

    static function API_GET_DATA($hostname, $cookie, $api, $version, $plain = false): array
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' . $api;

        try {
            if($plain) {
                $response = Http::accept('text/plain')->withoutVerifying()->withHeaders([
                    'Content-Type' => 'application/json',
                    'Cookie' => "$cookie",
                ])->get($api_url);
            } else {
                $response = Http::withoutVerifying()->withHeaders([
                    'Content-Type' => 'application/json',
                    'Cookie' => "$cookie",
                ])->get($api_url);
            }

            if ($response->successful()) {
                return ['success' => true, 'data' => ($plain) ? $response->body() : $response->json()];
            } else {
                return ['success' => false, 'data' => $response->json()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'data' => $e->getMessage()];
        }
    }

    static function API_PATCH_DATA($hostname, $cookie, $api, $version, $data): array
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' . $api;

        try {
            $response = Http::withBody($data, 'application/json')->withoutVerifying()->withHeaders([
                'Cookie' => "$cookie",
            ])->patch($api_url);

            if ($response->successful()) {
                return ['success' => true, 'data' => array($response->status(), $response->json())];
            } else {
                return ['success' => false, 'data' => $response->json()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'data' => $e->getMessage()];
        }
    }

    static function API_DELETE_DATA($hostname, $cookie, $api, $version, $data): array
    {
        // $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' . $api;

        // try {
        // } catch (\Exception $e) {
        //     return ['success' => false, 'data' => []];
        // }

        // NOT NEEDED YET

        return ['success' => false, 'data' => []];
    }

    static function API_POST_DATA($hostname, $cookie, $api, $version, $data): array
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' . $api;

        try {
            $response = Http::withBody($data, 'application/json')->withoutVerifying()->withHeaders([
                'Cookie' => "$cookie",
            ])->post($api_url);

            if ($response->successful()) {
                return ['success' => true, 'data' => array($response->status(), $response->json())];
            } else {
                return ['success' => false, 'data' => $response->json()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'data' => $e->getMessage()];
        }
    }

    static function API_REQUEST_ALL_DATA($device): array
    {
        if (!$device) {
            return ['success' => false, 'data' => __('DeviceNotFound'), 'message' => "Device not found"];
        }

        if (!$login_info = self::API_LOGIN($device)) {
            return ['success' => false, 'data' => __('Msg.ApiLoginFailed'), 'message' => "API Login failed"];
        }

        $data = [];

        list($cookie, $api_version) = explode(";", $login_info);

        foreach (self::$available_apis as $key => $api) {
            $api_data = self::API_GET_DATA($device->hostname, $cookie, $api, $api_version);

            if ($api_data['success']) {
                $data[$key] = $api_data['data'];
            }
        }

        $data = [
            'informations' => self::formatSystemData($data['status']),
            'vlans' => self::formatVlanData($data['vlans']),
            'ports' => self::formatPortData($data['ports'], $data['portstats']),
            'vlanports' => self::formatPortVlanData($data['vlanport']),
            'statistics' => self::formatExtendedPortStatisticData($data['portstats'], $data['ports']),
            'macs' => self::formatMacTableData([], $data['vlans'], $device, $cookie, $api_version),
            'uplinks' => self::formatUplinkData($data['ports']),
            'success' => true,
        ];

        self::API_LOGOUT($device->hostname, $cookie, $api_version);


        // $data = self::getSnmpData($device);

        return $data;
    }

    static function snmpFormatPortData(Array $ports, Array $stats): array {
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

    static function snmpFormatExtendedPortStatisticData(Array $portstats, Array $portdata): array {
        // Incompatible with DellEMC
        return [];
    }

    static function snmpFormatPortVlanData(Array $vlanports): array {
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

    static function snmpFormatUplinkData($data): array
    {
        $uplinks = [];

        foreach ($data['ports'] as $port) {
            if (isset($port['tagged']) && count($port['tagged']) >= count($data['vlans'])-10) {
                $uplinks[$port['name']] = $port['name'];
            }
        }

        return $uplinks;
    }


    static function snmpFormatVlanData(Array $vlans): array {
        $return = [];

        if (empty($vlans) or !is_array($vlans) or !isset($vlans)) {
            return $return;
        }

        foreach ($vlans as $key => $vlan) {
            if($vlan['description'] != "") 
            {
                $return[$key] = $vlan['description'];
            }
        }

        return $return;
    }

    static function snmpFormatMacTableData(Array $data, Array $vlans, Device $device, String $cookie, String $api_version): array {
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

            if(isset($vlans[$vlan[10]])) {
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
    
    static function snmpFormatSystemData(Array $system): array {
        $return = [];

        if (empty($system) or !is_array($system) or !isset($system)) {
            return [];
        }

        // dd($system);

        $hostname = str_replace("STRING: ", "", $system['hostname']);
        $hostname = str_replace("\"", "", $hostname);

        $sys_data = str_replace("STRING: ", "", $system['data']);
        $sys_data = str_replace(["\"", "\r", "revision "], "", $sys_data);
        $sys_data = explode(", ", $sys_data);

        $model = $sys_data[0];
        $version = $sys_data[1];

        // dd($system['uptime']);
        $uptime = str_replace("Timeticks: (", "", $system['uptime']);
        $uptime = strstr($uptime, ")", true);
        $uptime = ($uptime / 100) * 1000;

        // dd($uptime);

        $return = [
            'name' => $hostname,
            'model' => $model,
            'serial' => $system['serial_number'] ?? 'unknown',
            'firmware' => $version,
            'hardware' => $system['hardware'] ?? 'unknown',
            'mac' => null,
            'uptime' => $uptime ?? 0,
        ];

        return $return;
    }

    static function formatUplinkData($ports): array
    {
        $uplinks = [];
        foreach ($ports as $port) {
        }

        return $uplinks;
    }

    static function formatVlanData($vlans): array
    {
        $return = [];

        if (empty($vlans) or !is_array($vlans) or !isset($vlans)) {
            return $return;
        }

        foreach ($vlans as $vlan) {
            $return[$vlan['id']] = $vlan['name'];
        }

        return $return;
    }

    static function formatMacTableData($data = null, $vlans = [], $device, $cookie, $api_version): array
    {
        $vlan_macs = [];
        foreach ($vlans as $vlan) {
            $url = "system/vlans/" . $vlan['id'] . "/macs?attributes=port,mac_addr&depth=2";
            $api_data = self::API_GET_DATA($device->hostname, $cookie, $url, $api_version);
            if ($api_data['success']) {
                $vlan_macs[$vlan['id']] = $api_data['data'];
            }
        }

        $return = [];
        foreach ($vlan_macs as $key => $macs) {
            foreach ($macs as $mac) {
                $mac_filtered = str_replace(":", "", strtolower($mac['mac_addr']));

                // skip if mac-port ist empty
                if (empty($mac['port']) || !isset($mac['port'])) {
                    continue;
                }

                $return[$mac_filtered] = [
                    // Expecting 1/1/1 as Port, so we need to explode the key
                    'port' => explode("/", key($mac['port']))[2],
                    'mac' => $mac_filtered,
                    'vlan' => $key,
                ];
            }
        }

        return $return;
    }

    static function formatSystemData($system): array
    {
        if (empty($system) or !is_array($system) or !isset($system)) {
            return [];
        }

        $return = [
            'name' => $system['hostname'],
            'model' => $system['subsystems']['chassis,1']['product_info']['product_name'],
            'serial' => $system['subsystems']['chassis,1']['product_info']['serial_number'],
            'firmware' => $system['software_version'],
            'hardware' => $system['subsystems']['chassis,1']['product_info']['part_number'],
            'mac' => strtolower(str_replace(":", "", $system['subsystems']['chassis,1']['product_info']['base_mac_address'])),
        ];

        return $return;
    }

    static function formatPortData(array $ports, array $stats): array
    {
        $return = [];

        if (empty($ports) or !is_array($ports) or !isset($ports)) {
            return $return;
        }

        foreach ($ports as $port) {
            if ($port['ifindex'] < 1000) {
                $return[$port['ifindex']] = [
                    'name' => $port['description'],
                    'id' => $port['ifindex'],
                    'link' => ($port['link_state'] == "up") ? true : false,
                    'trunk_group' => null,
                    'vlan_mode' => $port['vlan_mode'] ?? "access",
                ];
            }
        }

        foreach ($stats as $stat) {
            if ($stat['ifindex'] < 1000) {
                $return[$stat['ifindex']]['speed'] = ($stat['link_speed'] != 0) ? $stat['link_speed'] / 1000000 : 0;
            }
        }

        return $return;
    }

    static function formatExtendedPortStatisticData($portstats, $portdata): array
    {
        $return = [];

        if (empty($portstats) or !is_array($portstats) or !isset($portstats)) {
            return $return;
        }

        foreach ($portstats as $port) {
            if ($port['ifindex'] < 1000) {
                $packets_tx = (isset($port['statistics']['tx_packets'])) ? $port['statistics']['tx_packets'] : 0;
                $packets_rx = (isset($port['statistics']['rx_packets'])) ? $port['statistics']['rx_packets'] : 0;
                $no_errors = (isset($port['statistics']['total_packets_no_errors'])) ? $port['statistics']['total_packets_no_errors'] : 0;
                $errors = $packets_rx + $packets_tx - $no_errors;

                $return[$port['ifindex']] = [
                    "id" => $port['ifindex'],
                    "name" => $port['description'] ?? '',
                    "port_speed_mbps" => ($port['link_speed'] != 0) ? $port['link_speed'] / 1000000 : 0,
                    "port_tx_packets" => $packets_tx,
                    "port_rx_packets" => $packets_rx,
                    "port_tx_bytes" => (isset($port['statistics']['tx_bytes'])) ? $port['statistics']['tx_bytes'] : 0,
                    "port_rx_bytes" => (isset($port['statistics']['rx_bytes'])) ? $port['statistics']['rx_bytes'] : 0,
                    "port_tx_errors" => $errors,
                    "port_rx_errors" => $errors,
                    "port_tx_bps" => (isset($port['rate_statistics']['tx_bytes_per_second'])) ? $port['rate_statistics']['tx_bytes_per_second'] : 0,
                    "port_rx_bps" => (isset($port['rate_statistics']['rx_bytes_per_second'])) ? $port['rate_statistics']['rx_bytes_per_second'] : 0,
                ];
            }
        }

        foreach($portdata as $port) {
            if ($port['ifindex'] < 1000) {
                $return[$port['ifindex']]['port_status'] = ($port['link_state'] == "up") ? true : false;
            }
        }

        return $return;
    }

    static function formatPortVlanData($vlanports): array
    {
        $return = [];

        if (empty($vlanports) or !is_array($vlanports) or !isset($vlanports)) {
            return $return;
        }

        $i = 0;
        foreach ($vlanports as $vlanport) {
            if ($vlanport['ifindex'] < 1000) {
                if ($vlanport['vlan_mode'] == "native-untagged") { // Untagged und erlaubte als trunks
                    $untagged_vlan = $vlanport['vlan_tag'] ?? [];
                    $tagged_vlans = $vlanport['vlan_trunks'] ?? [];


                    if (is_array($tagged_vlans) and count($tagged_vlans) == 0) {
                        // Man kann davon ausgehen, dass es ein Trunk ist
                        $return[$i] = [
                            "port_id" => $vlanport['ifindex'],
                            "vlan_id" => "Trunk",
                            "is_tagged" => true,
                        ];
                        $i++;
                    }

                    foreach ($tagged_vlans as $tagged_key => $tagged) {
                        $return[$i] = [
                            "port_id" => $vlanport['ifindex'],
                            "vlan_id" => $tagged_key,
                            "is_tagged" => true,
                        ];
                        $i++;
                    }

                    if (!empty($untagged_vlan)) {
                        $return[$i] = [
                            "port_id" => $vlanport['ifindex'],
                            "vlan_id" => key($untagged_vlan),
                            "is_tagged" => false,
                        ];
                        $i++;
                    }
                }
            }
        }

        return $return;
    }

    static function createBackup($device): bool
    {
        if (!$login_info = self::API_LOGIN($device)) {
            BackupController::store(false, false, false, $device);
            return false;
        }

        list($cookie, $api_version) = explode(";", $login_info);

        $data = self::API_GET_DATA($device->hostname, $cookie, "configs/running-config", $api_version);
        $dataraw = self::API_GET_DATA($device->hostname, $cookie, "configs/running-config", $api_version, true);

        self::API_LOGOUT($device->hostname, $cookie, $api_version);

        if ($data['success'] && $dataraw['success']) {
            $json = json_encode($data['data'], true);
            $plain = $dataraw['data'];

            if (strlen($json) > 10) {
                BackupController::store(true, $plain, $json, $device);
                return true;
            }
        }

        BackupController::store(false, false, false, $device);
        return false;
    }

    static function restoreBackup($device, $backup, $password_switch): array
    {
        if ($password_switch != Crypt::decrypt($device->password)) {
            return ['success' => false, 'data' => 'Wrong password'];
        }

        if (!$login_info = self::API_LOGIN($device)) {
            return ['success' => false, 'data' => __('Msg.ApiLoginFailed')];
        }

        list($cookie, $api_version) = explode(";", $login_info);

        $https = config('app.https');

        $api_url = $https . $device->hostname . '/rest/' . $api_version . '/configs/running-config';


        $data = [
            "configuration" => $backup->restore_data,
        ];

        $restore = Http::connectTimeout(30)->withoutVerifying()->withHeaders([
            'Cookie' => $cookie,
        ])->put($api_url, $data);

        if ($restore->status() == 200 && $restore->successful()) {
            return ['success' => true, 'data' => 'Restore successful'];
        }

        self::API_LOGOUT($device->hostname, $cookie, $api_version);

        return ['success' => false, 'data' => 'Restore failed: ' . $restore->json() . " (" . $restore->status() . ")"];
    }

    static function uploadPubkeys($device, $pubkeys): String
    {
        if (!$login_info = self::API_LOGIN($device)) {
            return json_encode(['success' => 'false', 'message' => 'Login failed']);
        }

        list($cookie, $api_version) = explode(";", $login_info);

        $https = config('app.https');

        $api_url = $https . $device->hostname . '/rest/' . $api_version . '/system/users/' . config('app.api_username');

        $upload = Http::withoutVerifying()->withHeaders([
            'Cookie' => $cookie,
            'Content-Type'  => 'application/json',
        ])->patch($api_url, array(
            'authorized_keys' => $pubkeys,
        ));

        // No need to enable public key auth, it's already enabled by default on ArubaCX
        // see https://www.arubanetworks.com/techdocs/AOS-CX/10.10/HTML/security_83xx-8400-9300-10000/Content/Chp_Loc_AAA/Loc_AAA_cmds/ssh-pub-key-aut62.htm

        self::API_LOGOUT($device->hostname, $cookie, $api_version);

        if ($upload->successful()) {
            return json_encode(['success' => 'true', 'message' => __('Pubkeys.Sync.Success')]);
        } else {
            return json_encode(['success' => 'false', 'message' => __('Pubkeys.Sync.Error')]);
        }
    }

    static function setUntaggedVlanToPort($newVlan, $port, $device, $vlans, $need_login = true, $logindata = ""): array
    {
        $success = $failed = 0;

        if ($need_login) {
            $login_info = self::API_LOGIN($device);
        } else {
            $login_info = $logindata;
        }

        if ($login_info) {

            list($cookie, $api_version) = explode(";", $login_info);

            $rest_vlans_uri = "/rest/" . $api_version . "/system/vlans/";

            $data = '{
                "vlan_mode": "' . $port->vlan_mode . '",
                "vlan_tag": "' . $rest_vlans_uri . $vlans[$newVlan]['vlan_id'] . '",
                "vlan_trunks": [
                    "' . $rest_vlans_uri . $vlans[$newVlan]['vlan_id'] . '"
                ]
                }';

            $uri = self::$port_if_uri . $port->name;

            $result = self::API_PATCH_DATA($device->hostname, $cookie, $uri, $api_version, $data);

            if ($result['success']) {
                $old = $port->untaggedVlan();

                if ($old) {
                    DeviceVlanPort::where('device_vlan_id', $old)->where('device_port_id', $port->id)->where('device_id', $device->id)->where('is_tagged', false)->delete();
                }

                DeviceVlanPort::updateOrCreate(
                    ['device_id' => $device->id, 'device_port_id' => $port->id, 'device_vlan_id' => $vlans[$newVlan]['id'], 'is_tagged' => false],
                );
                DeviceVlanPort::updateOrCreate(
                    ['device_id' => $device->id, 'device_port_id' => $port->id, 'device_vlan_id' => $vlans[$newVlan]['id'], 'is_tagged' => true],
                );

                return ['success' => true, 'data' => ''];
            } else {
                return ['success' => false, 'data' => $result['data']];
            }

            if ($need_login) {
                self::API_LOGOUT($device->hostname, $cookie, $api_version);
            }
        }
    }

    static function setTaggedVlansToPort($taggedVlans, $port, $device, $vlans, $need_login = true, $logindata = ""): array
    {
        $return = [];
        $uri = self::$port_if_uri . $port;

        if ($need_login) {
            $login_info = self::API_LOGIN($device);
        } else {
            $login_info = $logindata;
        }

        if ($login_info) {

            list($cookie, $api_version) = explode(";", $login_info);
            $rest_vlans_uri = "/rest/" . $api_version . "/system/vlans/";

            $untaggedVlan = $device->vlanports()->where('device_port_id', $port->id)->where('is_tagged', false)->first();    // Get all tagged vlans from port

            $data_builder = [];
            $data_builder['vlan_trunks'] = [];
            $data_builder['vlan_mode'] = "native-untagged";

            if (isset($untaggedVlan->device_vlan_id)) {
                $data_builder['vlan_tag'] = $rest_vlans_uri . $vlans[$untaggedVlan->device_vlan_id]['vlan_id'] ?? $rest_vlans_uri . "1";
            }

            foreach ($taggedVlans as $vlan) {
                if (!in_array($rest_vlans_uri . $vlans[$vlan]['vlan_id'], $data_builder['vlan_trunks'])) {
                    $data_builder['vlan_trunks'][] = $rest_vlans_uri . $vlans[$vlan]['vlan_id'];
                }
            }

            $data = json_encode($data_builder);

            $uri = self::$port_if_uri . $port->name;
            $result = self::API_PUT_DATA($device->hostname, $cookie, $uri, $api_version, $data);

            if ($result['success']) {
                $old = DeviceVlanPort::where('device_port_id', $port->id)->where('device_id', $device->id)->where('is_tagged', true);
                if ($old) {
                    $old->delete();
                }

                foreach ($taggedVlans as $vlan) {
                    $return[] = ['success' => true, 'data' => ''];
                    DeviceVlanPort::updateOrCreate(
                        ['device_id' => $device->id, 'device_port_id' => $port->id, 'device_vlan_id' => $vlans[$vlan]['id'], 'is_tagged' => true],
                    );
                }

                $return[] = ['success' => true, 'data' => ''];
            } else {
                foreach ($taggedVlans as $vlan) {
                    $return[] = ['success' => false, 'data' => ''];
                }
            }

            if ($need_login) {
                proc_open('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' > /dev/null &', [], $pipes);
                self::API_LOGOUT($device->hostname, $cookie, $api_version);
            }

            return $return;
        }

        $return[] = [
            'success' => false,
            'message' => __('Msg.ApiLoginFailed'),
        ];

        Log::channel('database')->error(__('Msg.ApiLoginFailed') . " " . $device->hostname);

        return $return;
    }

    static function syncVlans($syncable_vlans, $current_vlans, $device, $create_vlans, $rename_vlans, $testmode): array
    {

        $start = microtime(true);
        $not_found = $chg_name = [];

        $i_not_found = $i_chg_name = $i_vlan_created = $i_vlan_chg_name = 0;
        $return = [];

        foreach ($syncable_vlans as $key => $vlan) {
            if (!array_key_exists($key, $current_vlans)) {
                $not_found[$key] = $vlan;
                $i_not_found++;
            } else {
                if ($vlan['name'] != $current_vlans[$key]['name']) {
                    $chg_name[$key] = $vlan;
                    $i_chg_name++;
                }
            }
        }

        if ($i_not_found == 0 && $i_chg_name == 0) {
            return [];
        }

        if (!$testmode && !$login_info = self::API_LOGIN($device)) {
            return [];
        }

        if (!$testmode) {
            list($cookie, $api_version) = explode(";", $login_info);
        }

        if ($create_vlans) {
            foreach ($not_found as $key => $vlan) {
                $return[$vlan->vid]['name'] = $vlan->name;

                $data = '{
                    "name": "' . $vlan->name . '",
                    "id": ' . $vlan->vid . '
                }';

                if (!$testmode) {
                    // $response = self::API_POST_DATA($device->hostname, $cookie, "system/vlans", $api_version, $data);
                } else {
                    $response['success'] = true;
                }

                if ($response['success']) {
                    $i_vlan_created++;
                }

                $return[$vlan->vid]['created'] = $response['success'];
            }
        }

        if ($rename_vlans) {
            foreach ($chg_name as $vlan) {
                $return[$vlan->vid]['old'] = $current_vlans[$vlan->vid]['name'];
                $return[$vlan->vid]['name'] = $current_vlans[$vlan->vid]['name'];

                $data = '{
                    "name": "' . $vlan->name . '"
                }';

                if (!$testmode) {
                    $response = self::API_PUT_DATA($device->hostname, $cookie, "system/vlans/" . $vlan->vid, $api_version, $data);
                } else {
                    $response['success'] = true;
                }

                if ($response['success']) {
                    $i_vlan_chg_name++;
                    $return[$vlan->vid]['name'] = $vlan->name;
                }
                $return[$vlan->vid]['changed'] = ($response['success']) ? true : false;
            }
        }

        if (!$testmode) {
            $logdata = [
                "summary" => [
                    "created" => $i_vlan_created, 
                    "renamed" => $i_vlan_chg_name
                ],
                "vlans" => $return
            ];
    
            CLog::info("Sync", "VLANs synced on device " . $device->name, $device, json_encode($logdata));
            
            self::API_LOGOUT($device->hostname, $cookie, $api_version);
        }

        return $return;
    }

    static function setPortName($port, $name, $device, $logininfo)
    {
        list($cookie, $api_version) = explode(";", $logininfo);

        $data = '{
            "description": "' . $name . '"
        }';

        $response = self::API_PATCH_DATA($device->hostname, $cookie, self::$port_if_uri . $port, $api_version, $data);

        if ($response['success']) {
            return true;
        } else {
            return false;
        }
    }
}
