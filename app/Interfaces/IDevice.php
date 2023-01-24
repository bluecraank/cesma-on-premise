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

        static function API_GET_VERSIONS(Device $device): string;

        static function API_LOGIN(Device $device): string;

        static function API_LOGOUT(String $hostname, String $cookie, String $api_version): bool;

        static function API_PUT_DATA(String $hostname, String $cookie, String $api, String $api_version, String $data): Array;

        static function API_GET_DATA(String $hostname, String $cookie, String $api, String $api_version): Array;

        static function API_POST_DATA(String $hostname, String $cookie, String $api, String $api_version, String $data): Array;

        static function API_DELETE_DATA(String $hostname, String $cookie, String $api, String $api_version, String $data): Array;
        
        static function API_PATCH_DATA(String $hostname, String $cookie, String $api, String $api_version, String $data): Array;

        static function API_REQUEST_ALL_DATA(Device $device): Array;

        static function createBackup(Device $device): bool;

        static function restoreBackup(Device $device, Backup $backup, String $password): Array;

        static function formatPortData(Array $ports): Array;
    
        static function formatPortSimpleStatisticData(Array $portstats): Array;

        static function formatPortVlanData(Array $vlanports): Array;
    
        static function formatVlanData(Array $vlans): Array; 

        static function formatMacTableData(Array $macs, Device $device, $cookie, $api_version): Array;
        
        static function formatSystemData(Array $system): Array;

        static function getDeviceTrunks(Device $device): Array;

        static function uploadPubkeys($device, $pubkeys): String;

        static function setUntaggedVlanToPort($vlans, $ports, $device): String;

        static function setTaggedVlanToPort($vlans, $ports, $device): Array;

        static function syncVlans(Vlan $vlans, Array $vlans_of_switch, Device $device, Bool $create_vlans, Bool $overwrite_name,  Bool $test_mode): Array;
    }
?>