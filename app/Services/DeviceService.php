<?php

namespace App\Services;

use App\Helper\CLog;
use App\Models\Device;
use App\Models\Client;
use App\Models\DeviceBackup;
use App\Models\DevicePort;
use App\Models\DevicePortStat;
use App\Models\DeviceUplink;
use App\Models\DeviceVlan;
use App\Models\DeviceVlanPort;
use App\Models\Mac;
use App\Models\Notification;
use Illuminate\Http\Request;

class DeviceService
{
    static function refreshDevice(Device $device, $type = "snmp")
    {
        $class = config('app.types')[$device->type];
        $response = $class::GET_DEVICE_DATA($device, $type);

        if (isset($response['success']) and $response['success']) {
            self::storeApiData($response, $device);
            return json_encode([
                'success' => "true",
                'message' => __('Successfully refreshed device'),
            ]);
        } else {
            $response = $class::GET_DEVICE_DATA($device, "api");
            if (isset($response['success']) and $response['success']) {
                self::storeApiData($response, $device);
                return json_encode([
                    'success' => "true",
                    'message' => __('Successfully refreshed device'),
                ]);
            }
        }

        return json_encode([
            'success' => "false",
            'message' => __('Device could not be refreshed'),
        ]);
    }

    static function storeApiData($data, $device)
    {
        $device->touch('last_seen');

        // Update device system informations
        UpdateDeviceData::updateDeviceSystemInfo($data['informations'], $device);

        // Update device ports
        UpdateDeviceData::updateDevicePorts($data['ports'], $device);

        // Update device vlans
        UpdateDeviceData::updateDeviceVlans($data['vlans'], $device);

        // Update device vlan ports
        $new_uplinks = UpdateDeviceData::updateVlanPorts($data['vlanports'], $device);
        $uplinks = array_merge($data['uplinks'], $new_uplinks['uplinks']);

        // Update device uplinks
        $found_uplinks = UpdateDeviceData::updateDeviceUplinks($data['uplinks'], $uplinks, $device);

        // Update device port statistics
        UpdateDeviceData::updateDevicePortStatistics($data['statistics'], $device);

        // Update mac data
        UpdateDeviceData::updateMacData($data['macs'], $found_uplinks, $device);

        // Check for uplinks
        UpdateDeviceData::checkForUplinks($device, $found_uplinks);

        $device->save();
    }

    static function deleteDeviceData($id, $ports)
    {
        DeviceBackup::where('device_id', $id)->delete();
        DeviceUplink::where('device_id', $id)->delete();
        Notification::where('unique-identifier', 'LIKE', '%-'.$id.'-%')->delete();
        DeviceVlanPort::where('device_id', $id)->delete();
        DevicePortStat::where(function ($query) use ($ports) {
            foreach ($ports as $port) {
                $query->orWhere('device_port_id', $port);
            }
        })->delete();
        DevicePort::where('device_id', $id)->delete();
        DeviceVlan::where('device_id', $id)->delete();
        Client::where('device_id', $id)->delete();
        Mac::where('device_id', $id)->delete();
    }

    static function storeUplink(Device $device, Request $request)
    {
        // Update/Create uplinks via notification
        if ($request->has("id") && $request->has("a")) {
            $notification = Notification::find($request->id);

            if (!$notification) {
                return redirect()->back()->withErrors(['message' => __('Something went wrong')]);
            }

            $data = json_decode($notification->data, true);
            $port = DevicePort::where('device_id', $data['device_id'])->where('name', $data['port'])->first();

            if($request["a"] == "yes" && $port) {
                DeviceUplink::updateOrCreate([
                    'name' => $data['port'],
                    'device_id' => $data['device_id'],
                    'device_port_id' => $port->id,
                ]);

                CLog::info("Device", "Added Port ".$data['port']." as uplink for device {$device->name}");

                $notification->update([
                    'status' => 'accepted',
                ]);

                return redirect()->back()->with('success', __('Uplink added'));
            } else {
                $notification->update([
                    'status' => 'declined',
                ]);

                CLog::info("Device", "Declined Port ".$data['port']." as uplink for device {$device->name}");

                return redirect()->back()->with('success', __('Uplink declined'));
            }

        }

        return redirect()->back()->withErrors(['message' => __('Something went wrong')]);
    }

    static function updatePortDescription(String $cookie, DevicePort $port, $device_id, $newDescription)
    {

        $device = Device::find($device_id);

        if (!$device) {
            return false;
        }

        $class = config('app.types')[$device->type];

        if (!$cookie) {
            return false;
        }

        return $class::setPortDescription($port, $newDescription, $device, $cookie);
    }

    static function updatePortUntaggedVlan(String $cookie, DevicePort $port, $device_id, $untaggedVlan)
    {
        $device = Device::find($device_id);

        if (!$device) {
            return false;
        }

        $class = config('app.types')[$device->type];

        $vlans = $device->vlans()->get()->keyBy('id')->toArray();

        if (!$cookie) {
            return false;
        }

        return $class::setUntaggedVlanToPort($untaggedVlan, $port, $device, $vlans, false, $cookie);
    }

    static function updatePortTaggedVlans(String $cookie, DevicePort $port, $device_id, $taggedVlans)
    {
        $device = Device::find($device_id);

        if (!$device) {
            return false;
        }

        $class = config('app.types')[$device->type];

        $vlans = $device->vlans()->get()->keyBy('id')->toArray();

        if (!$cookie) {
            return false;
        }

        $result = $class::setTaggedVlansToPort($taggedVlans, $port, $device, $vlans, false, $cookie);

        return $result;
    }

    static function closeApiSession($cookie, $id)
    {
        $device = Device::find($id);
        if (!$device) {
            return json_encode(['success' => 'false', 'message' => 'Device not found']);
        }
        $class = config('app.types')[$device->type];
        list($cookie, $api_version) = explode(";", $cookie);
        $class::API_LOGOUT($device, $cookie, $api_version);
    }
}
