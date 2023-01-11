<?php

namespace App\Devices;

use App\Http\Controllers\BackupController;
use App\Http\Controllers\DeviceController;
use App\Interfaces\IDevice;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\EncryptionController;
use App\Models\Backup;
use App\Models\Device;
use Illuminate\Http\Client\Request;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

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
        // "mac" => 'system/vlans?attributes=name,id,macs&depth=3',
    ];

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
            $response = Http::withoutVerifying()->asForm()->post($api_url, [
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
        
        $logout = Http::withoutVerifying()->withHeaders([
            'Cookie' => "$cookie",
        ])->post($api_url);

        return true;
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

    public function test($id) {
        $device = Device::find($id);
        // $device->hostname = $hostname;
        // return DeviceController::uploadPubkeys($device);
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
                $trunks[] = "1/1/".$port['port_id'];
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

    static function uploadPubkeys($device, $pubkeys) {
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

        self::ApiLogout($device->hostname, $cookie, $api_version);

        if($upload->successful()) {
            return json_encode(['success' => 'true', 'error' => 'Pubkeys synced']);
        } else {
            return json_encode(['success' => 'false', 'error' => 'Pubkeys not synced']);
        }

    }
}

?>