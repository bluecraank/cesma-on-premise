<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Client;
use App\Models\DeviceBackup;
use App\Models\DeviceCustomUplink;
use App\Models\DevicePort;
use App\Models\DevicePortStat;
use App\Models\DeviceUplink;
use App\Models\DeviceVlan;
use App\Models\DeviceVlanPort;
use App\Models\Mac;
use App\Models\Site;
use App\Models\Vlan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceService
{
    static function refreshDevice(Device $device, $type = "snmp")
    {
        $response = config('app.types')[$device->type]::GET_DEVICE_DATA($device, $type);

        if (isset($response['success']) and $response['success']) {
            self::storeApiData($response, $device);
            return json_encode([
                'success' => "true",
                'message' => __('Msg.RefreshedDevice'),
            ]);
        }

        return json_encode([
            'success' => "false",
            'message' => __('Msg.FailedToRefreshDevice'),
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
        array_merge($data['uplinks'], $new_uplinks['uplinks']);

        // Update device uplinks
        $combined_uplinks = UpdateDeviceData::updateDeviceUplinks($data['uplinks'], $device);

        // Update device port statistics
        UpdateDeviceData::updateDevicePortStatistics($data['statistics'], $device);

        // Update mac data
        UpdateDeviceData::updateMacData($data['macs'], $combined_uplinks, $device);

        $device->save();
    }

    static function deleteDeviceData(Device $device)
    {
        $ports = $device->ports()->get();
        DeviceBackup::where('device_id', $device->id)->delete();
        DeviceUplink::where('device_id', $device->id)->delete();
        DeviceCustomUplink::where('device_id', $device->id)->delete();
        DeviceVlanPort::where('device_id', $device->id)->delete();
        DevicePortStat::where(function ($query) use ($ports) {
            foreach ($ports as $port) {
                $query->orWhere('device_port_id', $port->id);
            }
        })->delete();
        DevicePort::where('device_id', $device->id)->delete();
        DeviceVlan::where('device_id', $device->id)->delete();
        Client::where('device_id', $device->id)->delete();
        Mac::where('device_id', $device->id)->delete();
    }

    static function isOnline($hostname)
    {
        try {
            if ($fp = fsockopen($hostname, 22, $errCode, $errStr, 0.2)) {
                fclose($fp);
                return true;
            }
            fclose($fp);
        } catch (\Exception $e) {
        }

        return false;
    }

    static function storeCustomUplinks(Request $request)
    {
        if ($request->has('uplinks') and $request->device_id != '') {

            if (preg_match("/[^A-Za-z0-9\,\-]/", $request->uplinks)) {
                return back()->withErrors(['error' => 'Format error (allowed: 1-10 or 1,2,3,4,5)']);
            }

            // Wenn eine Range angegeben wurde (z.B 49-52)
            if (str_contains($request->uplinks, "-")) {
                $explode_range = preg_split('@-@', $request->uplinks, -1, PREG_SPLIT_NO_EMPTY);
                $uplinks = range($explode_range[0], $explode_range[1]);

                // Sonst einfach kommasepariert (z.B 49,50,51,52)
            } else {
                $explode_range = preg_split('@,@', $request->uplinks, -1, PREG_SPLIT_NO_EMPTY);
                $uplinks = $explode_range;
            }

            $uplinks = str_replace(' ', '', $uplinks);

            $uplinks = json_encode($uplinks);

            DeviceCustomUplink::updateOrCreate(
                [
                    'device_id' => $request->device_id
                ],
                [
                    'uplinks' => $uplinks
                ]
            );

            return back()->with('success', __('Msg.UplinkUpdated'));
        }

        return back()->with('error', __('Msg.UplinkNotUpdated'));
    }

    static function updatePortDescription($logininfo, $port, $device_id, $newDescription)
    {

        $device = Device::find($device_id);

        if (!$device) {
            return false;
        }

        $class = config('app.types')[$device->type];

        if (!$logininfo) {
            return false;
        }

        return $class::setPortName($port->name, $newDescription, $device, $logininfo);
    }


    static function syncVlansToAllDevices(Request $request)
    {
        $site_id = Auth::user()->currentSite()->id;

        $devices = Site::find($site_id)->devices()->get()->keyBy('id');
        $syncable_vlans = Vlan::where('is_synced', '!=', '0')->where('site_id', $site_id)->get()->keyBy('vid');

        $results = [];

        $create_vlans = ($request->input('create-if-not-exists') == "on") ? true : false;
        $rename_vlans = ($request->input('overwrite-vlan-name') == "on") ? true : false;

        $testmode = ($request->input('test-mode') == "on") ? true : false;

        $start = microtime(true);

        foreach ($devices as $device) {
            if (!in_array($device->type, array_keys(config('app.types')))) {
                continue;
            }

            $current_vlans = $device->vlans()->get()->keyBy('vlan_id')->toArray();

            $results[$device->id] = [];

            $class = config('app.types')[$device->type];
            $results[$device->id] = $class::syncVlans($syncable_vlans, $current_vlans, $device, $create_vlans, $rename_vlans, $testmode);
        }

        $elapsed = microtime(true) - $start;

        return view('vlan.view_sync-results', compact('devices', 'results', 'elapsed', 'create_vlans', 'rename_vlans', 'testmode', 'site_id'));
    }

    static function syncVlansToDevice(Device $device, Request $request)
    {
        $devices = Device::all()->keyBy('id');
        $site_id = $device->site_id;
        $current_vlans = $device->vlans()->get()->keyBy('vlan_id')->toArray();
        $syncable_vlans = Vlan::where('is_synced', '!=', '0')->where('site_id', $device->site_id)->get()->keyBy('vid');

        $results = [];

        $create_vlans = ($request->input('create-if-not-exists') == "on") ? true : false;
        $rename_vlans = ($request->input('overwrite-vlan-name') == "on") ? true : false;
        $testmode = ($request->input('test-mode') == "on") ? true : false;

        $start = microtime(true);

        $results[$device->id] = [];
        $class = config('app.types')[$device->type];
        $results[$device->id] = $class::syncVlans($syncable_vlans, $current_vlans, $device, $create_vlans, $rename_vlans, $testmode);

        $elapsed = microtime(true) - $start;

        return view('vlan.view_sync-results', compact('devices', 'results', 'elapsed', 'testmode', 'create_vlans', 'rename_vlans', 'site_id'));
    }

    static function updatePortVlans(String $cookie, DevicePort $port, $device_id, $untaggedVlan, $taggedVlans, $untaggedIsUpdated, $taggedIsUpdated)
    {

        $device = Device::find($device_id);

        $class = config('app.types')[$device->type];

        $login_info = $cookie;

        $vlans = $device->vlans()->get()->keyBy('id')->toArray();

        if (!$login_info) {
            return false;
        }

        $return = [];
        if ($untaggedIsUpdated) {
            $success_untagged = $class::setUntaggedVlanToPort($untaggedVlan, $port, $device, $vlans, false, $login_info);
            $return['untagged'] = $success_untagged;
        }

        if ($taggedIsUpdated) {
            $success_tagged = $class::setTaggedVlansToPort($taggedVlans, $port, $device, $vlans, false, $login_info);
            $return['tagged'] = $success_tagged;
        }

        list($cookie, $api_version) = explode(";", $login_info);
        $class::API_LOGOUT($device, $cookie, $api_version);

        return $return;
    }

    // Prevent mass login on switch api, use global cookie instead
    // Execute way faster
    public function startApiSession($id, Request $request)
    {

        $device = Device::find($id);

        if(config('app.write_type')[$device->type] == "snmp") {
            return json_encode(['success' => 'true', 'hash' => Crypt::encrypt("SNMP_NO_LOGIN"), 'timestamp' => time()]);
        }

        if (!$device) {
            return json_encode(['success' => 'false', 'message' => __('DeviceNotFound')]);
        }

        $class = config('app.types')[$device->type];

        $login_info = $class::API_LOGIN($device);
        if (!$login_info) {
            return json_encode(['success' => 'false', 'message' => 'Failed login']);
        }

        return json_encode(['success' => 'true', 'hash' => Crypt::encrypt($login_info), 'timestamp' => time()]);
    }

    static function closeApiSession($cookie, $id)
    {
        $device = Device::find($id);
        if (!$device) {
            return json_encode(['success' => 'false', 'message' => __('DeviceNotFound')]);
        }
        $class = config('app.types')[$device->type];
        list($cookie, $api_version) = explode(";", $cookie);
        $class::API_LOGOUT($device, $cookie, $api_version);
    }
}
