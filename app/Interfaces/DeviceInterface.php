<?php 

    namespace App\Interfaces;

    use App\Models\Device;
    use App\Models\DeviceBackup;
    use App\Models\Vlan;

    interface DeviceInterface
    {

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

        static function restoreBackup(Device $device, DeviceBackup $backup, String $password): Array;

        static function formatPortData(Array $ports, Array $stats): Array;
    
        static function formatPortSimpleStatisticData(Array $portstats): Array;

        static function formatPortVlanData(Array $vlanports): Array;
    
        static function formatVlanData(Array $vlans): Array; 

        static function formatMacTableData(Array $macs): Array;
        
        static function formatSystemData(Array $system): Array;

        static function uploadPubkeys($device, $pubkeys): String;

        static function setUntaggedVlanToPort($vlans, $ports, $device): String;

        static function setTaggedVlanToPort($vlans, $ports, $device): Array;

        static function syncVlans(Vlan $vlans, Array $vlans_of_switch, Device $device, Bool $create_vlans, Bool $overwrite_name,  Bool $test_mode): Array;
    }
