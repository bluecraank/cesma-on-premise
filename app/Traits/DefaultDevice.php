<?php
    namespace App\Traits;

    use App\Models\Device;
    use Illuminate\Support\Facades\Log;

    trait DefaultDevice
    {
        static function GET_DEVICE_DATA(Device $device, $type = "snmp"): array
        {
            try {
                if (self::$fetch_from['snmp'] && $type == "snmp") {
                    return self::getSnmpData($device);
                }

                if (self::$fetch_from['api'] && $type == "api") {
                    return self::getApiData($device);
                }
            } catch (\Throwable $e) {
                // e.g. device stops answering during the snmp walks
                Log::warning("[Device] Fetching {$type} data of device {$device->id} failed: " . $e->getMessage());
            }

            return ['message' => 'Failed to get data from device', 'success' => false];
        }
    }
