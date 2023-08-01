<?php 

    namespace App\Interfaces;

    use App\Models\Device;
    use App\Models\DeviceBackup;
    use App\Models\Vlan;

    interface DeviceInterface
    {
        static function getSnmpData(Device $device): Array;

        static function getApiData(Device $device): Array;

        static function API_GET_VERSIONS(Device $device): string;

        static function API_LOGIN(Device $device): string;

        static function API_LOGOUT(String $hostname, String $cookie, String $api_version): bool;

        static function API_PUT_DATA(String $hostname, String $cookie, String $api, String $api_version, String $data): Array;

        static function API_GET_DATA(String $hostname, String $cookie, String $api, String $api_version, Bool $plain): Array;

        static function API_POST_DATA(String $hostname, String $cookie, String $api, String $api_version, String $data): Array;

        static function API_DELETE_DATA(String $hostname, String $cookie, String $api, String $api_version, String $data): Array;
        
        static function API_PATCH_DATA(String $hostname, String $cookie, String $api, String $api_version, String $data): Array;

        static function GET_DEVICE_DATA(Device $device, String $type): Array;

        static function createBackup(Device $device): bool;

        static function restoreBackup(Device $device, DeviceBackup $backup, String $password): Array;

        static function formatPortData(Array $ports, Array $stats): Array;

        static function formatExtendedPortStatisticData(Array $portstats, Array $portdata): Array;

        static function formatPortVlanData(Array $vlanports): Array;

        static function formatUplinkData(Array $ports): array;
    
        static function formatVlanData(Array $vlans): Array; 

        static function formatMacTableData(Array $data, Array $vlans, Device $device, String $cookie, String $api_version): Array;
        
        static function formatSystemData(Array $system): Array;

        static function snmpFormatPortData(Array $ports, Array $stats): Array;

        static function snmpFormatExtendedPortStatisticData(Array $portstats, Array $portdata): Array;

        static function snmpFormatPortVlanData(Array $vlanports): Array;

        static function snmpFormatUplinkData(Array $ports): array;
    
        static function snmpFormatVlanData(Array $vlans): Array; 

        static function snmpFormatMacTableData(Array $data, Array $vlans, Device $device, String $cookie, String $api_version): Array;
        
        static function snmpFormatSystemData(Array $system): Array;

        static function uploadPubkeys($device, $pubkeys): String;

        static function setUntaggedVlanToPort($vlan, $port, $device, $vlans, $need_login, $login_info): Array;

        static function setTaggedVlansToPort($taggedVlans, $port, $device, $vlans, $need_login, $login_info): Array;

        static function syncVlans(Vlan $vlans, Array $vlans_of_switch, Device $device, Bool $create_vlans, Bool $overwrite_name, Bool $tag_to_uplinks, Bool $test_mode): Array;
    }
