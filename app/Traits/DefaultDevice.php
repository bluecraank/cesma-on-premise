<?php
    namespace App\Traits;

    use App\Models\Device;

    trait DefaultDevice
    {        
        static function GET_DEVICE_DATA(Device $device, $type = "snmp"): array
        {
            if (self::$fetch_from['snmp'] && $type == "snmp") {
                return self::getSnmpData($device);
            }

            if (self::$fetch_from['api'] && $type == "api") {
                return self::getApiData($device);
            }

            return ['message' => 'Failed to get data from device', 'success' => false];
        }
    }