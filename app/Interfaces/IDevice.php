<?php 

    namespace App\Interfaces;

    use App\Models\Device;
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

        static function ApiGet(String $hostname, String $cookie, String $api, String $api_version): Array;

        static function ApiGetAcceptPlain(String $hostname, String $cookie, String $api, String $api_version): Array;

        static function getApiData(Device $device): Array;

        static function createBackup(Device $device): bool;

        static function restoreBackup(Request $request): bool;

        static function getPortData(Array $ports): Array;
    
        static function getPortStatisticData(Array $portstats): Array;

        static function getVlanPortData(Array $vlanports): Array;
    
        static function getVlanData(Array $vlans): Array; 

        static function getMacTableData(Array $macs): Array;
        
        static function getSystemInformations(Array $system): Array;

        static function getTrunks(Device $device): Array;
    }
?>