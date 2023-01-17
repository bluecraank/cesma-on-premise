<?php 

    namespace App\Interfaces;

use App\Models\Backup;
use App\Models\Device;
use App\Models\Vlan;
use Illuminate\Http\Client\Request;

    interface IDevice
    {
        /* 
         * Get all available API versions
         * 
         * @property Array $available_apis
         * 
         */

        static function GetApiVersions(Device $device): string;

        static function ApiLogin(Device $device): string;

        static function ApiLogout(String $hostname, String $cookie, String $api_version): bool;

        static function ApiPut(String $hostname, String $cookie, String $api, String $api_version, String $data): Array;

        static function ApiGet(String $hostname, String $cookie, String $api, String $api_version): Array;

        static function ApiPost(String $hostname, String $cookie, String $api, String $api_version, String $data): Array;

        static function ApiDelete(String $hostname, String $cookie, String $api, String $api_version, String $data): Array;
        
        static function ApiPatch(String $hostname, String $cookie, String $api, String $api_version, String $data): Array;

        static function ApiGetAcceptPlain(String $hostname, String $cookie, String $api, String $api_version): Array;

        static function getApiData(Device $device): Array;

        static function createBackup(Device $device): bool;

        static function restoreBackup(Device $device, Backup $backup, String $password): Array;

        static function getPortData(Array $ports): Array;
    
        static function getPortStatisticData(Array $portstats): Array;

        static function getVlanPortData(Array $vlanports): Array;
    
        static function getVlanData(Array $vlans): Array; 

        static function getMacTableData(Array $macs, Device $device, $cookie, $api_version): Array;
        
        static function getSystemInformations(Array $system): Array;

        static function getTrunks(Device $device): Array;

        static function uploadPubkeys($device, $pubkeys): String;

        static function updatePortVlanUntagged($vlans, $ports, $device): String;

        static function updatePortVlanTagged($vlans, $ports, $device): Array;

        static function updateVlans(Vlan $vlans, Array $vlans_of_switch, Device $device, Bool $create_vlans, Bool $test_mode): Array;
    }
?>