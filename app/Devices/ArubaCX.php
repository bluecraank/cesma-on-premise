<?php

namespace App\Devices;

use App\Interfaces\IDevice;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\EncryptionController;
use App\Models\Backup;
use App\Models\Device;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

class ArubaCX implements IDevice
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
        "mac" => 'mac-table',
    ];

    static function GetApiVersions($hostname): string
    {
        $https = config('app.https');
        $url = $https . $hostname . '/rest/version';

        $versions = Http::withoutVerifying()->get($url);

        if($versions->successful()) {
            $versionsFound = $versions->json()['version_element'];
            return $versionsFound[array_key_last($versionsFound)]['version'];
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
            $response = Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json'
            ])->retry(2,200, throw: false)->post($api_url, [
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
        ])->delete($api_url . 'login-sessions');

        return true;
    }   

    static function ApiGet($hostname, $cookie, $api, $version): Array
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' .$api;

        $response = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
            'Cookie' => "$cookie",
        ])->get($api_url);

        if($response->successful()) {
            return ['success' => true, 'data' => $response->json()];
        } else {
            return ['success' => false, 'data' => $response->json()];
        }
    }   

    static function getApiData($device): Array
    {
        if(!$device) {
            return ['success' => false, 'data' => 'Device not found'];
        }

        $data = [];

        if(!$login_info = self::ApiLogin($device)) {
            //ddd($login_info);
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

        self::ApiLogout($device->hostname, $cookie, $api_version);

        $system_data = self::getSystemInformations($data['status']);
        $vlan_data = self::getVlanData($data['vlans']['vlan_element']);
        $port_data = self::getPortData($data['ports']['port_element']);
        $portstat_data = self::getPortStatisticData($data['portstats']['port_statistics_element']);
        $vlanport_data = self::getVlanPortData($data['vlanport']['vlan_port_element']);
        $mac_data = self::getMacTableData($data['mac']['mac_table_entry_element']);

        //ddd($system_data, $vlan_data, $port_data, $portstat_data, $vlanport_data, $mac_data);

        return [
            'system' => $system_data,
            'vlans' => $vlan_data,
            'ports' => $port_data,
            'portstats' => $portstat_data,
            'vlanport' => $vlanport_data,
            'mac' => $mac_data,
        ];
    }

    public function test($device) {
        return self::getApiData($device);
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

    static function getMacTableData($macs): Array
    {
        $return = [];

        if(empty($macs) or !is_array($macs) or !isset($macs)) {
            return $return;
        }

        foreach($macs as $mac) {
            $mac_filtered = str_replace("-", "", strtolower($mac['mac_address']));
            $return[$mac_filtered] = [
                'mac' => $mac_filtered,
                'port' => $mac['port_id'],
                'vlan' => $mac['vlan_id'],
            ];
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
            'mac' => strtolower($system['base_ethernet_address']['octets']),
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

        foreach($vlanports as $vlanport) {
            $return[$vlanport['port_id']] = [
                "port_id" => $vlanport['port_id'],
                "vlan_id" => $vlanport['vlan_id'],
                "is_tagged" => ($vlanport['port_mode'] == "POM_TAGGED_STATIC") ? true : false,
            ];
        }

        return $return;
    }

    static function createBackup($device): bool
    {
        if (config('app.ssh_private_key')) {
            $privatekey = EncryptionController::getPrivateKey();
            if($privatekey !== NULL) {
                $key = PublicKeyLoader::load($privatekey);
            } else {
                return false;
            }
        } else {
            $key = EncryptionController::decrypt($device->password);
        }
        
        try {
            $sftp = new SFTP($device->hostname);
            $sftp->login(config('app.ssh_username'), $key);
            $data = $sftp->get('/cfg/running-config');
    
            if($data === NULL or strlen($data) < 10 or $data == false) {
                Backup::create([
                    'device_id' => $device->id,
                    'data' => "No data received",
                    'status' => 0,
                ]);
            } elseif(strlen($data) > 10) {
                Backup::create([
                    'device_id' => $device->id,
                    'data' => $data,
                    'status' => 1,
                ]);

                return true;
            }
        } catch (\Exception $e) {
            Backup::create([
                'device_id' => $device->id,
                'data' => $e->getMessage(),
                'status' => 0,
            ]);
        }
        
        return false;
    }

    public function restoreBackup($backup_id): bool
    {
        return true;
    }
}

?>