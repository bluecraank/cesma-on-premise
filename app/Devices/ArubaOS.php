<?php

namespace App\Devices;

use App\Interfaces\IDevice;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\EncryptionController;
use App\Http\Controllers\KeyController;
use App\Models\Backup;
use App\Models\Device;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;
use Spatie\FlareClient\Api;

class ArubaOS implements IDevice
{
    static $api_auth = [
        "login" => "login-sessions",
        "logout" => "login-sessions",
    ];

    static $available_apis = [
        "status" => 'system/status',
        "vlans" => 'vlans',
        "ports" => 'ports',
        "portstats" => 'port-statistics',
        "vlanport" => 'vlans-ports',
    ];

    static function GetApiVersions($hostname): string
    {
        $https = config('app.https');
        $url = $https . $hostname . '/rest/version';

        try {
        $versions = Http::withoutVerifying()->get($url);

        if($versions->successful()) {
            $versionsFound = $versions->json()['version_element'];
            return $versionsFound[array_key_last($versionsFound)]['version'];
        }
        } catch (\Exception $e) {
        }

        return "v7";
    }

    static function ApiLogin($device): string
    {
        $api_version = self::GetApiVersions($device->hostname);

        $api_url = config('app.https') . $device->hostname . '/rest/' . $api_version . '/' . self::$api_auth['login'];
 
        $api_username = config('app.api_username');
        $api_password = EncryptionController::decrypt($device->password);

        try {
            $response = Http::connectTimeout(3)->withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json'
            ])->post($api_url, [
                'userName' => $api_username,
                'password' => $api_password,
            ]);

            // Return cookie if login was successful
            if($response->successful() AND !empty($response->json()['cookie'])) {
                return $response->json()['cookie'].";".$api_version;
            }
        } catch (\Exception $e) {
            return "";
        }
        return "";
    }

    static function ApiLogout($hostname, $cookie, $api_version): bool
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $api_version . '/' . self::$api_auth['logout'];
        
        $logout = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
            'Cookie' => "$cookie",
        ])->delete($api_url);

        return true;
    }   

    static function ApiPut($hostname, $cookie, $api, $version, $data): Array
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' .$api;

        try {
            $response = Http::withBody($data, 'application/json')->withoutVerifying()->withHeaders([
                'Cookie' => "$cookie",
            ])->put($api_url);

            if($response->successful()) {
                return ['success' => true, 'data' => array($response->status(), $response->json())];
            } else {
                return ['success' => false, 'data' => $response->json()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'data' => []];
        }
    }

    static function ApiGet($hostname, $cookie, $api, $version): Array
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' .$api;

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
                'Cookie' => "$cookie",
            ])->get($api_url);

            if($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            } else {
                return ['success' => false, 'data' => $response->json()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'data' => []];
        }
    } 

    static function ApiPatch($hostname, $cookie, $api, $version, $data): Array
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' .$api;

        try {
            $response = Http::withBody($data, 'application/json')->withoutVerifying()->withHeaders([
                'Cookie' => "$cookie",
            ])->patch($api_url);

            if($response->successful()) {
                return ['success' => true, 'data' => array($response->status(), $response->json())];
            } else {
                return ['success' => false, 'data' => $response->json()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'data' => []];
        }
    }

    static function ApiPost($hostname, $cookie, $api, $version, $data): Array
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' .$api;

        try {
            $response = Http::withBody($data, 'application/json')->withoutVerifying()->withHeaders([
                'Cookie' => "$cookie",
            ])->post($api_url);

            if($response->successful()) {
                return ['success' => true, 'data' => array($response->status(), $response->json())];
            } else {
                return ['success' => false, 'data' => $response->json()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'data' => []];
        }
    }   

    static function ApiGetAcceptPlain($hostname, $cookie, $api, $version) : Array {
        $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' .$api;
 
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Accept' => 'text/plain',
                'Cookie' => "$cookie",
            ])->get($api_url);

            if($response->successful()) {
                return ['success' => true, 'data' => $response->body()];
            } else {
                return ['success' => false, 'data' => "Error while fetching $api"];
            } 
        } catch (\Exception $e) {
            return ['success' => false, 'data' => []];
        }   
    }

    static function getApiData($device): Array
    {
        if(!$device) {
            return ['success' => false, 'data' => 'Device not found'];
        }

        $data = [];

        if(!$login_info = self::ApiLogin($device)) {
            return ['success' => false, 'data' => 'Login failed'];
        }

        list($cookie, $api_version) = explode(";", $login_info);

        foreach(self::$available_apis as $key => $api) {
            $api_data = self::ApiGet($device->hostname, $cookie, $api, $api_version);
            
            $data[$key] = "[]";
            if($api_data['success']) {
                $data[$key] = $api_data['data'];
            }
        }

        // self::ApiLogout($device->hostname, $cookie, $api_version);

        $system_data = self::getSystemInformations($data['status']);
        $vlan_data = self::getVlanData($data['vlans']['vlan_element']);
        $port_data = self::getPortData($data['ports']['port_element']);
        $portstat_data = self::getPortStatisticData($data['portstats']['port_statistics_element']);
        $vlanport_data = self::getVlanPortData($data['vlanport']['vlan_port_element']);
        $mac_data = self::getMacTableData($data['vlans']['vlan_element'], $device, $cookie, $api_version);

        self::ApiLogout($device->hostname, $cookie, $api_version);

        return [
            'sysstatus_data' => $system_data,
            'vlan_data' => $vlan_data,
            'ports_data' => $port_data,
            'portstats_data' => $portstat_data,
            'vlanport_data' => $vlanport_data,
            'mac_table_data' => $mac_data,
        ];
    }

    static function getVlanData($vlans): Array
    {
        $return = [];

        if(empty($vlans) or !is_array($vlans) or !isset($vlans)) {
            return $return;
        }

        foreach($vlans as $vlan) {
            $return[$vlan['vlan_id']] = [
                'name' => $vlan['name'],
                'vlan_id' => $vlan['vlan_id'],
            ];
        }

        return $return;
    }

    static function getMacTableData($vlans, $device, $cookie, $api_version): Array
    {
        $core_switches = [
            1 => 'CORE1',
            2 => 'CORE2'
        ];

        $return = []; 
        if (in_array($device->name, $core_switches)) {
            $vlan_macs = [];
            foreach($vlans as $vlan) {
                $url = "vlans/".$vlan['vlan_id']."/mac-table";
                $api_data = self::ApiGet($device->hostname, $cookie, $url, $api_version);
                if($api_data['success']) {
                    $vlan_macs[$vlan['vlan_id']] = $api_data['data']['mac_table_entry_element'];
                }
            }

            foreach($vlan_macs as $key => $macs) {
                foreach($macs as $mac) {
                    $mac_filtered = str_replace([":", "-"], "", strtolower($mac['mac_address']));
                    $return[$mac_filtered] = [
                        'port' => $mac['port_id'],
                        'mac' => $mac_filtered,
                        'vlan' => $key,
                    ];
                }
            }
        } else {
            $uri = "mac-table";
            $api_data = self::ApiGet($device->hostname, $cookie, $uri, $api_version);

            foreach($api_data['data']['mac_table_entry_element'] as $mac) {
                $mac_filtered = str_replace([":", "-"], "", strtolower($mac['mac_address']));
                $return[$mac_filtered] = [
                    'port' => $mac['port_id'],
                    'mac' => $mac_filtered,
                    'vlan' => $mac['vlan_id'],
                ];
            }
        }

        return $return;
    }

    static function getSystemInformations($system): Array
    {
        if(isset($system['name']) and $system['name'] != "") {
            $return = [
            'name' => "AOS-UNKNOWN",
            'model' => "Unknown",
            'serial' => "Unknown",
            'firmware' => "Unknown",
            'hardware' => "Unknown",
            'mac' => "000000000000", 
            ];
        }

        $return = [
            'name' => $system['name'],
            'model' => $system['product_model'],
            'serial' => $system['serial_number'],
            'firmware' => $system['firmware_version'],
            'hardware' => $system['hardware_revision'],
            'mac' => strtolower(str_replace("-", "", $system['base_ethernet_address']['octets'])),
        ];

        return $return;
    }

    static function getPortData(Array $ports): Array
    {
        $return = [];

        if(empty($ports) or !is_array($ports) or !isset($ports)) {
            return $return;
        }

        foreach($ports as $port) {
            $return[$port['id']] = [
                'name' => $port['name'],
                'id' => $port['id'],
                'is_port_up' => $port['is_port_up'],
                'trunk_group' => $port['trunk_group'],
            ];
        }

        return $return;
    }

    static function getPortStatisticData($portstats): Array
    {
        $return = [];

        if(empty($portstats) or !is_array($portstats) or !isset($portstats)) {
            return $return;
        }

        foreach($portstats as $port) {
            $return[$port['id']] = [
                "id" => $port['id'],
                "name" => $port['name'],
                "port_speed_mbps" => $port['port_speed_mbps'],
            ];
        }

        return $return;
    }

    static function getVlanPortData($vlanports): Array
    {
        $return = [];

        if(empty($vlanports) or !is_array($vlanports) or !isset($vlanports)) {
            return $return;
        }

        $i = 0;
        foreach($vlanports as $vlanport) {
            $return[$i] = [
                "port_id" => $vlanport['port_id'],
                "vlan_id" => $vlanport['vlan_id'],
                "is_tagged" => ($vlanport['port_mode'] == "POM_TAGGED_STATIC") ? true : false,
            ];

            $i++;
        }

        return $return;
    }

    static function getTrunks($device): Array {
        $trunks = [];
        $ports = json_decode($device->port_data, true);
        foreach($ports as $port) {
            if(str_contains($port['id'], "Trk")) {
                $trunks[] = $port['id'];
            }
        }

        return $trunks;
    }

    static function createBackup($device): bool
    {
        if(!$login_info = self::ApiLogin($device)) {
            return false;
        }

        list($cookie, $api_version) = explode(";", $login_info);

        $https = config('app.https');

        $api_url = $https . $device->hostname . '/rest/' . $api_version . '/cli';

        $backup = Http::withoutVerifying()->asJson()->withHeaders([
            'Cookie' => $cookie,
        ])->post($api_url, array(
            'cmd' => 'show running-config',
        ));

        self::ApiLogout($device->hostname, $cookie, $api_version);

        if($backup->successful()) {
            $data = $backup->json()["result_base64_encoded"];
            $data = base64_decode($data);
            $data = str_replace("Running configuration:", "", $data);
            $data = strstr($data, ";") or $data;
            if($data !== NULL and strlen($data) > 10 and $data != false) {
                Backup::create([
                    'device_id' => $device->id,
                    'data' => $data,
                    'status' => 1,
                ]);
                return true;
            } else {
                Backup::create([
                    'device_id' => $device->id,
                    'data' => "No data received",
                    'status' => 0,
                ]);
                return false;
            }
        } else {
            Backup::create([
                'device_id' => $device->id,
                'data' => "No data received",
                'status' => 0,
            ]);
            return false;
        }
    }

    static function restoreBackup($device, $backup, $password_switch): Array
    {
        if($password_switch != EncryptionController::decrypt($device->password)) {
            return ['success' => false, 'data' => 'Wrong password for switch'];
        }

        if(!$login_info = self::ApiLogin($device)) {
            return ['success' => false, 'data' => 'Login failed'];
        }

        list($cookie, $api_version) = explode(";", $login_info);

        $https = config('app.https');

        $api_url = $https . $device->hostname . '/rest/' . $api_version . '/system/config/cfg_restore/payload';

        $restore = Http::withoutVerifying()->withHeaders([
            'Cookie' => $cookie,
        ])->post($api_url, [
            'config_base64_encoded' => base64_encode($backup->data),
            'is_forced_reboot_enabled' => false,
            'is_recoverymode_enabled' => false,
        ]);

        if(isset($restore->json()['status']) and $restore->json()['status'] == "CRS_SUCCESS") {
            return ['success' => true, 'data' => 'Restore successful'];
        }

        while (true) {
            sleep(4);
            $status = self::ApiGet($device->hostname, $cookie, '/system/config/cfg_restore/payload/status', $api_version);
            $data = $status['data'];

            if(isset($data['status']) and $data['status'] == "CRS_SUCCESS") {
                return ['success' => true, 'data' => 'Restore successful'];
            }

            if(isset($data['status']) and $data['status'] != "CRS_IN_PROGRESS") {
                break;
            }
        }

        self::ApiLogout($device->hostname, $cookie, $api_version);
        
        return ['success' => false, 'data' => 'Restore failed: '.$data['status'] . " " .$data['failure_reason']];
    }

    static function uploadPubkeys($device, $pubkeys = false): String {

        if (config('app.ssh_private_key')) {
            $decrypt = EncryptionController::getPrivateKey();
            if($decrypt !== NULL) {
                $key = PublicKeyLoader::load($decrypt);
            } else {
                return json_encode(['success' => 'false', 'error' => 'Error private key']);
            }
        } else {
            $key = EncryptionController::decrypt($device->password);
        }       

        $keys = KeyController::getPubkeys();
        if($keys != "") {
            try {
                $sftp = new SFTP($device->hostname);
                $sftp->login(config('app.ssh_username'), "fridolin");
                $upload = $sftp->put('/ssh/mgr_keys/authorized_keys', $keys);
                $sftp->disconnect();

                if($upload) {
                    if($login_info = self::ApiLogin($device))  {
                        list($cookie, $api_version) = explode(";", $login_info);
            
                        $url = "authentication/ssh";

                        $data = '{
                            "auth_ssh_login": {
                                "primary_method": "PAM_PUBLIC_KEY"
                            },
                            "auth_ssh_enable": {
                                "primary_method": "PAM_PUBLIC_KEY"
                            }
                        }';
            
                        $response = self::ApiPut($device->hostname, $cookie, $url, $api_version, $data);

                        self::ApiLogout($device->hostname, $cookie, $api_version);
                        if($response['success']) {
                            return json_encode(['success' => 'true', 'error' => "Uploaded and aaa configured"]);
                        }
                    }
                }

                return json_encode(['success' => 'true', 'error' => $upload]);
            } catch (\Exception $e) {
                return json_encode(['success' => 'false', 'error' => 'Error sftp connection '.$e->getMessage()]);
            }
        }

        return json_encode(['success' => 'false', 'error' => 'Error sftp connection']);
    }

    static function updatePortVlanUntagged($vlans, $ports, $device): String {
        
        $success = 0;
        $failed = 0;
        $failed_ports = [];
        $portcount = count($ports);

        if($login_info = self::ApiLogin($device)) {

            list($cookie, $api_version) = explode(";", $login_info);

            $uri = "vlans-pors";

            foreach($ports as $key => $port) {
                $data = '{
                    "vlan_id": '.$vlans[$key].', 
                    "port_id": "'.$port.'", 
                    "port_mode":"POM_UNTAGGED"
                }';

                $result = self::ApiPost($device->hostname, $cookie, $uri, $api_version, $data);

                if($result['success']) {
                    $success++;
                } else {
                    $failed++;
                    $failed_ports[] = "[".$port."] => VLAN-". $vlans[$key];
                }
            }

            if($failed !== count($ports)) {
                $newVlanPortData = self::ApiGet($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version)['data'];
                $device->vlan_port_data =  self::getVlanPortData($newVlanPortData['vlan_port_element']);
                $device->save();
            }

            self::ApiLogout($device->hostname, $cookie, $api_version);

            if($success == $portcount) {
                return json_encode(['success' => 'true', 'error' => 'Updated '.$success.' of '.$portcount.' ports']);
            } else {
                return json_encode(['success' => 'false', 'error' => 'Failed to update '.$failed.' of '.count($ports).' ports<br> '.implode("<br>", $failed_ports)]);
            }
        }

        return json_encode(['success' => 'false', 'error' => 'API Login failed']);
    }

    static function updateVlans($vlans, $vlans_switch, $device, $create_vlans, $test): Array {

        $start = microtime(true);
        $not_found = [];
        $chg_name = [];
        $return = [];
        $return['log'] = [];

        $i_not_found = 0;
        $i_chg_name = 0;

        $i_vlan_created = 0;
        $i_vlan_chg_name = 0;

        $return['log'][] = "<b>[Aufgaben]</b></br>";
        foreach($vlans as $key => $vlan) {
            if(!array_key_exists($key, $vlans_switch)) {
                $not_found[$key] = $vlan;
                $return['log'][] = "<span class='tag is-link'>VLAN $key</span> VLAN erstellen";
                $i_not_found++;
            } else {
                if($vlan->name != $vlans_switch[$key]['name']) {
                    $chg_name[$key] = $vlan;
                    $return['log'][] = "<span class='tag is-link'>VLAN $key</span> Name ändern ({$vlans_switch[$key]['name']} => {$vlan->name})";
                    $i_chg_name++;
                }
            }
        }

        if($i_not_found == 0 && $i_chg_name == 0) {
            $return['log'][] = "Keine Aufgaben";
            $return['time'] = number_format(microtime(true)-$start, 2);
            return $return;
        }   

        $return['log'][] = "</br><b>[Log]</b></br>";

        if(!$test) { 
            if(!$login_info = self::ApiLogin($device)) {
                $return['log'][] = "<span class='tag is-danger'>API</span> Login fehlgeschlagen";
                $return['time'] = number_format(microtime(true)-$start, 2);
                return $return;
            }

            list($cookie, $api_version) = explode(";", $login_info);

            if($create_vlans) {
                foreach($not_found as $key => $vlan) {
                    $data = '{
                        "vlan_id": '.$vlan->vid.',
                        "name": "'.$vlan->name.'"
                    }';
                    
                    $response = self::ApiPost($device->hostname, $cookie, "vlans", $api_version, $data);

                    if($response['success']) {
                        $return['log'][] = "<span class='tag is-success'>VLAN {$vlan->vid}</span> erfolgreich erstellt";
                        $i_vlan_created++;
                    } else {
                        $return['log'][] = "<span class='tag is-danger'>VLAN {$vlan->vid}</span> konnte nicht erstellt werden";
                    }
                }
            }

            foreach($chg_name as $vlan) {
                $data = '{
                    "vlan_id": '.$vlan->vid.', 
                    "name": "'.$vlan->name.'"
                }';
                
                $response = self::ApiPut($device->hostname, $cookie, "vlans/".$vlan->vid, $api_version, $data);

                if($response['success']) {
                    $return['log'][] = "<span class='tag is-success'>VLAN {$vlan->vid}</span> erfolgreich umbenannt";
                    $i_vlan_chg_name++;
                } else {
                    $return['log'][] = "<span class='tag is-danger'>VLAN {$vlan->vid}</span> konnte nicht umbenannt werden";
                }
            }

            $vlan_data = self::ApiGet($device->hostname, $cookie, self::$available_apis['vlans'], $api_version)['data'];
            $device->vlan_data = self::getVlanData($vlan_data['vlan_element']);
            $device->save();

            self::ApiLogout($device->hostname, $cookie, $api_version);
        }
        
        $return['log'][] = "</br><b>[Ergebnisse]</b></br>";
        $return['log'][] = $i_vlan_created." von ".$i_not_found." VLANs erstellt";
        $return['log'][] = $i_vlan_chg_name." von ".$i_chg_name." VLANs umbenannt";

        $return['time'] = number_format(microtime(true)-$start, 2);
        return $return;
    }
}

?>