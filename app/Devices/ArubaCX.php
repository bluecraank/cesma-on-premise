<?php

namespace App\Devices;

use App\Interfaces\IDevice;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\EncryptionController;
use App\Models\Backup;

class ArubaCX implements IDevice
{
    static $api_auth = [
        "login" => "login",
        "logout" => "logout",
    ];

    static $available_apis = [
        "status" => '/system?attributes=software_version,subsystems,hostname&depth=3',
        "subsystem" => 'system/subsystems/chassis,1?attributes=product_info',
        "vlans" => 'system/vlans?attributes=name,id&depth=2',
        "ports" => 'system/interfaces?attributes=ifindex,link_state,description&depth=2',
        "portstats" => 'system/interfaces?attributes=ifindex,link_speed,description&depth=2',
        "vlanport" => 'system/interfaces?attributes=ifindex,vlan_mode,vlan_tag,vlan_trunks&depth=2',
    ];

    static $port_if_uri = "system/interfaces/1%2F1%2F";

    static function GetApiVersions($hostname): string
    {
        $https = config('app.https');
        $url = $https . $hostname . '/rest';

        try {
            $versions = Http::withoutVerifying()->get($url);

            if($versions->successful()) {
                $versionsFound = $versions->json()['latest'];
                return $versionsFound['version'];
            }
        } catch (\Exception $e) {
        }

        return "v10.04";
    }

    static function ApiLogin($device): string
    {
        $api_version = self::GetApiVersions($device->hostname);
        $api_url = config('app.https') . $device->hostname . '/rest/' . $api_version . '/' . self::$api_auth['login'];
 
        $api_username = config('app.api_username');
        $api_password = EncryptionController::decrypt($device->password);

        try {
            $response = Http::connectTimeout(3)->withoutVerifying()->asForm()->post($api_url, [
                'username' => $api_username,
                'password' => $api_password,
            ]); 

            // Return cookie if login was successful
            if($response->successful() AND !empty($response->header('Set-Cookie'))) {
                return $response->cookies()->toArray()[0]['Name']."=".$response->cookies()->toArray()[0]['Value'].";".$api_version;
            }

        } catch (\Exception $e) {
            return "";
        }
        return "";
    }

    static function ApiLogout($hostname, $cookie, $api_version): bool
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $api_version . '/' . self::$api_auth['logout'];
        
        try {
            $logout = Http::connectTimeout(8)->withoutVerifying()->withHeaders([
                'Cookie' => "$cookie",
            ])->post($api_url);

            if($logout->successful()) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }

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
            return ['success' => false, 'data' => $e->getMessage()];
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
                return ['success' => false, 'data' => "Error while fetching $api"];
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
            dd($e->getMessage());   
        }
    }

    static function ApiDelete($hostname, $cookie, $api, $version, $data): Array
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' .$api;

        try {

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
        $vlan_data = self::getVlanData($data['vlans']);
        $port_data = self::getPortData($data['ports']);
        $portstat_data = self::getPortStatisticData($data['portstats']);
        $vlanport_data = self::getVlanPortData($data['vlanport']);
        $mac_data = self::getMacTableData($data['vlans'], $device, $cookie, $api_version);

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
            $return[$vlan['id']] = [
                'name' => $vlan['name'],
                'vlan_id' => $vlan['id'],
            ];
        }

        return $return;
    }

    static function getMacTableData($vlans, $device, $cookie, $api_version): Array
    {
        $vlan_macs = [];
        foreach($vlans as $vlan) {
            $url = "system/vlans/".$vlan['id']."/macs?attributes=port,mac_addr&depth=2";
            $api_data = self::ApiGet($device->hostname, $cookie, $url, $api_version);
            if($api_data['success']) {
                $vlan_macs[$vlan['id']] = $api_data['data'];
            }
        }

        $return = []; 
        foreach($vlan_macs as $key => $macs) {
            foreach($macs as $mac) {
                $mac_filtered = str_replace(":", "", strtolower($mac['mac_addr']));
                $return[$mac_filtered] = [
                    'port' => explode("/", key($mac['port']))[2],
                    'mac' => $mac_filtered,
                    'vlan' => $key,
                ];
            }
        }

        return $return;
    }

    static function getSystemInformations($system): Array
    {
        if(isset($system['hostname']) and $system['hostname'] != "") {
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
            'name' => $system['hostname'],
            'model' => $system['subsystems']['chassis,1']['product_info']['product_name'],
            'serial' => $system['subsystems']['chassis,1']['product_info']['serial_number'],
            'firmware' => $system['software_version'],
            'hardware' => $system['subsystems']['chassis,1']['product_info']['part_number'],
            'mac' => strtolower(str_replace(":", "", $system['subsystems']['chassis,1']['product_info']['base_mac_address'])),
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
            if($port['ifindex'] < 1000) {
                $return[$port['ifindex']] = [
                    'name' => $port['description'],
                    'id' => $port['ifindex'],
                    'is_port_up' => ($port['link_state'] == "up") ? true : false,
                    'trunk_group' => null,
                ];
            }
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
            if($port['ifindex'] < 1000) {
                $return[$port['ifindex']] = [
                    "id" => $port['ifindex'],
                    "name" => $port['description'],
                    "port_speed_mbps" => $port['link_speed'] / 1000000,
                ];
            }
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
            if($vlanport['ifindex'] < 1000) {
                if($vlanport['vlan_mode'] == "native-untagged") {// Untagged und erlaubte als trunks
                    $untagged_vlan = $vlanport['vlan_tag'];
                    $tagged_vlans = $vlanport['vlan_trunks'];

                    if(is_array($tagged_vlans) and count($tagged_vlans) == 0) {
                        // Man kann davon ausgehen, dass es ein Trunk ist
                        $return[$i] = [
                            "port_id" => $vlanport['ifindex'],
                            "vlan_id" => "Trunk",
                            "is_tagged" => true,
                        ];
                        $i++;
                    }

                    foreach($tagged_vlans as $tagged_key => $tagged) {
                        $return[$i] = [
                            "port_id" => $vlanport['ifindex'],
                            "vlan_id" => $tagged_key,
                            "is_tagged" => true,
                        ];
                        $i++;
                    }

                    $return[$i] = [
                        "port_id" => $vlanport['ifindex'],
                        "vlan_id" => key($untagged_vlan),
                        "is_tagged" => false,
                    ];
                    $i++;
                }
            } 
        }

        return $return;
    }

    static function getTrunks($device): Array {

        $trunks = [];
        $ports = json_decode($device->vlan_port_data, true);
        foreach($ports as $port) {
            if(str_contains($port['vlan_id'], "Trunk")) {
                $trunks[] = $port['port_id'];
            }
        }        

        return $trunks;
    }

    static function createBackup($device): bool
    {   
        if(!$login_info = self::ApiLogin($device)) {
            ddd($login_info);
            return false;
        }

        list($cookie, $api_version) = explode(";", $login_info);

        $data = self::ApiGet($device->hostname, $cookie, "configs/running-config", $api_version);

        self::ApiLogout($device->hostname, $cookie, $api_version);

        if($data['success']) {
            $encoded = json_encode($data['data'], true);
            if(strlen($encoded) > 10) {
                Backup::create([
                    'device_id' => $device->id,
                    'data' => $encoded,
                    'status' => 1,
                ]);
                return true;
            } else {
                Backup::create([
                    'device_id' => $device->id,
                    'data' => "No data",
                    'status' => 0,
                ]);
                return false;
            }
        }

        return false;
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

    static function uploadPubkeys($device, $pubkeys): String {
        if(!$login_info = self::ApiLogin($device)) {
            return json_encode(['success' => 'false', 'error' => 'Login failed']);
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

        self::ApiLogout($device->hostname, $cookie, $api_version);

        if($upload->successful()) {
            return json_encode(['success' => 'true', 'error' => 'Pubkeys synced']);
        } else {
            return json_encode(['success' => 'false', 'error' => 'Pubkeys not synced']);
        }

    }

    static function updatePortVlanUntagged($vlans, $ports, $device): String {
        $success = 0;
        $failed = 0;
        $portcount = count($ports);

        if($login_info = self::ApiLogin($device)) {

            list($cookie, $api_version) = explode(";", $login_info);

            $rest_vlans_uri = "/rest/".$api_version."/system/vlans/";

            foreach($ports as $key => $port) {
                $data = '{
                    "vlan_mode": "native-untagged",
                    "vlan_tag": "'.$rest_vlans_uri.$vlans[$key].'",
                    "vlan_trunks": [
                      "'.$rest_vlans_uri.$vlans[$key].'"
                    ]
                  }';

                $uri = self::$port_if_uri.$port;
                $result = self::ApiPatch($device->hostname, $cookie, $uri, $api_version, $data);

                if($result['success']) {
                    $success++;
                } else {
                    $failed++;
                }
            }

            if($failed !== count($ports)) {
                $newVlanPortData = self::ApiGet($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version)['data'];
                $device->vlan_port_data =  self::getVlanPortData($newVlanPortData);
                $device->save();
            }

            self::ApiLogout($device->hostname, $cookie, $api_version);

            if($success == $portcount) {
                return json_encode(['success' => 'true', 'error' => 'Updated '.$success.' of '.$portcount.' ports']);
            } else {
                return json_encode(['success' => 'false', 'error' => 'Failed to update '.$failed.' of '.count($ports).' ports']);
            }
        }

        return json_encode(['success' => 'false', 'error' => 'Login failed']);
    }

    static function updatePortVlanTagged($vlans, $port, $device): Array {
        $return = [];
        $uri = "system/interfaces/1%2F1%2F".$port;

        if($login_info = self::ApiLogin($device)) {
    
            list($cookie, $api_version) = explode(";", $login_info);

            $data_builder = [];
            $data_builder['vlan_mode'] = "native-untagged";

            $rest_vlans_uri = "/rest/".$api_version."/system/vlans/";

            $alreadyTaggedVlans = json_decode($device->vlan_port_data, true);
            foreach($alreadyTaggedVlans as $taggedVlan) {
                if(!$taggedVlan['is_tagged'] and $taggedVlan['port_id'] == $port) {
                    $data_builder['vlan_trunks'][] = $rest_vlans_uri.$taggedVlan['vlan_id'];
                    $data_builder['vlan_tag'] = $rest_vlans_uri.$taggedVlan['vlan_id'];
                }
            }

            foreach($vlans as $vlan) {
                if(!in_array($rest_vlans_uri.$vlan, $data_builder['vlan_trunks'])) {
                    $data_builder['vlan_trunks'][] = $rest_vlans_uri.$vlan;
                }
            }
            
            $data = json_encode($data_builder);

            $uri = self::$port_if_uri.$port;
            $result = self::ApiPut($device->hostname, $cookie, $uri, $api_version, $data);

            if($result['success']) {
                $newVlanPortData = self::ApiGet($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version)['data'];
                $device->vlan_port_data =  self::getVlanPortData($newVlanPortData);
                $device->save();

                foreach($data_builder['vlan_trunks'] as $vlan) {
                    $vlan = explode("/", $vlan);
                    $vlan = end($vlan);
                    $return[] = [
                        'success' => true,
                        'error' => '['. $port .'] Tagged VLAN '.$vlan,
                    ];
                }
            } else {
                $return[] = [
                    'success' => false,
                    'error' => '['. $port .'] Not Tagged VLAN '. $vlan,
                ];
            }

            self::ApiLogout($device->hostname, $cookie, $api_version);
            return $return;
        
        }

        $return[] = [
            'success' => false,
            'error' => 'API Login failed',
        ];  

        return $return; 
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
                        "name": '.$vlan->name.',
                        "id": '.$vlan->vid.'
                    }';
                    
                    $response = self::ApiPost($device->hostname, $cookie, "system/vlans", $api_version, $data);

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
                    "name": "'.$vlan->name.'"
                }';
                
                $response = self::ApiPut($device->hostname, $cookie, "system/vlans/".$vlan->vid, $api_version, $data);

                if($response['success']) {
                    $return['log'][] = "<span class='tag is-success'>VLAN {$vlan->vid}</span> erfolgreich umbenannt";
                    $i_vlan_chg_name++;
                } else {
                    if($vlan->vid != 1) {
                        $return['log'][] = "<span class='tag is-danger'>VLAN {$vlan->vid}</span> konnte nicht umbenannt werden";
                    } else {
                        $return['log'][] = "<span class='tag is-danger'>VLAN {$vlan->vid}</span> VLAN 1 kann nicht umbenannt werden (ArubaCX Einschränkung)";
                    }
                }
            }

            $vlan_data = self::ApiGet($device->hostname, $cookie, self::$available_apis['vlans'], $api_version)['data'];
            $device->vlan_data = self::getVlanData($vlan_data);
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