<?php

namespace App\Services;

use App\Models\Device;
use App\Devices\ArubaOS;
use App\Devices\ArubaCX;
use App\Models\Client;
use App\Models\DeviceBackup;
use App\Models\DeviceCustomUplink;
use App\Models\DevicePort;
use App\Models\DevicePortStat;
use App\Models\DeviceUplink;
use App\Models\DeviceVlan;
use App\Models\DeviceVlanPort;
use App\Models\Location;
use App\Models\Mac;
use App\Models\Vlan;
use Illuminate\Support\Facades\Crypt;
use App\Services\VlanService;
use Illuminate\Http\Request;

class DeviceService
{
    static $types = [
        'aruba-os' => ArubaOS::class,
        'aruba-cx' => ArubaCX::class,
    ];

    static function refreshDevice(Device $device)
    {
        $api_data = self::$types[$device->type]::API_REQUEST_ALL_DATA($device);

        if (isset($api_data['success']) and $api_data['success']) {
            self::storeApiData($api_data, $device);
            return json_encode([
                'success' => "true",
                'message' => __('Msg.RefreshedDevice'),
            ]);
        }

        if(app()->runningInConsole()) {
            return json_encode([
                'success' => "false",
                'message' => $api_data['message'],
            ]);
        }

        return json_encode([
            'success' => "false",
            'message' => __('Msg.FailedToRefreshDevice'),
        ]);
    }

    static function storeApiData($data, $device)
    {
        if(count($data['vlans']) != $device->vlans()->count()) {
            $device->touch();
        }

        foreach ($data['vlans'] as $vid => $vname) {
            $device->vlans()->updateOrCreate(
                [
                    'vlan_id' => $vid,
                    'device_id' => $device->id
                ],
                [
                    'name' => $vname,
                ]
            );

            // Store vlan in global vlans table
            VlanService::createIfNotExists($device, $vid, $vname);
        }

        $ports = $device->ports();

        foreach ($data['ports'] as $port) {

            DevicePort::updateOrCreate(
                [
                    'name' => $port['id'],
                    'device_id' => $device->id
                ],
                [
                    'description' => $port['name'],
                    'link' => $port['link'],
                    'speed' => $port['speed'] ?? 0,
                    'vlan_mode' => $port['vlan_mode'],
                ]
            );
        }

        // $deleteOldVlanPorts = DeviceVlanPort::where('device_id', $device->id)->where('is_tagged', true)->where('updated_at', '<', Carbon::now()->subMinutes(6)->toDateTimeString())->delete();
        $currentVlanPorts = DeviceVlanPort::where('device_id', $device->id)->count();

        $newVlanPorts = 0;
        foreach($data['vlanports'] as $vlanport) {
            if($vlanport['vlan_id'] != "Trunk") {
                $newVlanPorts++;
            }
        }

        if($currentVlanPorts != $newVlanPorts) {
            DeviceVlanPort::where('device_id', $device->id)->delete();
            $device->touch();
        }
        
        foreach ($data['vlanports'] as $vlanport) {
            // CX Trunk Discovery
            if($vlanport['vlan_id'] == "Trunk") {
                $data['uplinks'][$vlanport['port_id']] = $vlanport['port_id'];
            } else {
                $test = $device->vlanports()->updateOrCreate(
                    [
                        'device_port_id' => $device->ports()->where('name', $vlanport['port_id'])->first()->id,
                        'device_id' => $device->id,
                        'device_vlan_id' => $device->vlans()->where('vlan_id', $vlanport['vlan_id'])->first()->id,
                        'is_tagged' => $vlanport['is_tagged']
                    ]
                );
            }
        }

        if(count($data['uplinks']) != $device->uplinks()->count()) {
            $device->touch();
        }

        foreach ($data['uplinks'] as $port => $uplink) {
            DeviceUplink::updateOrCreate([
                'name' => $uplink,
                'device_id' => $device->id,
                'device_port_id' => $device->ports()->where('name', $port)->first()->id,
            ]);
        }

        foreach ($data['statistics'] as $statistic) {
            DevicePortStat::create([
                'device_port_id' => $device->ports()->where('name', $statistic['id'])->first()->id,
                'port_speed' => $statistic['port_speed_mbps'] ?? 0,
                'port_rx_bps' => $statistic['port_rx_bps'] ?? 0,
                'port_tx_bps' => $statistic['port_tx_bps'] ?? 0,
                'port_rx_pps' => $statistic['port_rx_pps'] ?? 0,
                'port_tx_pps' => $statistic['port_tx_pps'] ?? 0,
                'port_rx_bytes' => $statistic['port_rx_bytes'] ?? 0,
                'port_tx_bytes' => $statistic['port_tx_bytes'] ?? 0,
                'port_rx_packets' => $statistic['port_rx_packets'] ?? 0,
                'port_tx_packets' => $statistic['port_tx_packets'] ?? 0,
                'port_rx_errors' => $statistic['port_rx_errors'] ?? 0,
                'port_tx_errors' => $statistic['port_tx_errors'] ?? 0
            ]);
        }

        // Get custom uplink ports
        $custom_uplink_ports = [];
        $uplinks = $device->uplinks()->get()->pluck('name')->toArray();
        $custom_uplinks = $device->custom_uplink()->first();
        if($custom_uplinks) {
            $custom_uplink_ports = json_decode($custom_uplinks->uplinks, true);
        }

        // Check if mac address is on uplink port
        $combined_uplinks = array_merge($uplinks, $custom_uplink_ports);
        foreach ($data['macs'] as $mac) {
            // Do not store macs on uplinks because its not correct discovered
            if(in_array($mac['port'], $combined_uplinks)) {
                // Store uplink mac address
                Mac::updateOrCreate(
                    [
                        'mac_address' => $mac['mac'],
                        'type' => 'uplink'
                    ],
                    [
                        'device_id' => $device->id,
                        'port_id' => $mac['port'],
                        'vlan_id' => $mac['vlan'],
                    ]
                );

                // Prevent storing same mac address twice
                continue;
            }

            // Update mac address otherwise create new
            // Update because if a mac address is moved to another port / switch it will be updated
            Mac::updateOrCreate(
                [
                    'mac_address' => $mac['mac'],
                    'type' => NULL
                ],
                [
                    'device_id' => $device->id,
                    'port_id' => $mac['port'],
                    'vlan_id' => $mac['vlan'],
                ]
            );
        }

        $device->named = $data['informations']['name'] ?? NULL;
        $device->model = $data['informations']['model'] ?? NULL;
        $device->serial = $data['informations']['serial'] ?? NULL;
        $device->hardware = $data['informations']['hardware'] ?? NULL;
        $device->mac_address = $data['informations']['mac'] ?? NULL;
        $device->firmware = $data['informations']['firmware'] ?? NULL;
        $device->save();
    }

    static function storeDevice($request)
    {

        // Get hostname from device else use ip
        $hostname = $request->input('hostname');
        if (filter_var($request->input('hostname'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $hostname = gethostbyaddr($request->input('hostname')) or $request->input('hostname');
        }
        $request->merge(['hostname' => $hostname]);

        // Encrypt password
        $request->merge(['password' => Crypt::encrypt($request->password)]);

        // Create device
        $device = Device::create($request->except('_token'));

        if ($device) {
            return $device;
        }

        return false;
    }

    static function deleteDeviceData(Device $device)
    {
        $ports = $device->ports()->get();
        DeviceBackup::where('device_id', $device->id)->delete();
        DeviceUplink::where('device_id', $device->id)->delete();
        DeviceCustomUplink::where('device_id', $device->id)->delete();
        DeviceVlanPort::where('device_id', $device->id)->delete();
        DevicePortStat::where(function($query) use ($ports) {
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

    static function updatePortDescription($logininfo, $port, $device_id, $newDescription) {

        $device = Device::find($device_id);

        if (!$device) {
            return false;
        }

        $class = self::$types[$device->type];
        
        if(!$logininfo) {
            return false;
        }

        return $class::setPortName($port->name, $newDescription, $device, $logininfo);
    }


    static function syncVlansToAllDevices(Request $request)
    {
        $location_id = $request->input('location_id');

        $devices = Location::find($location_id)->devices()->get()->keyBy('id');
        $syncable_vlans = Vlan::where('is_synced', '!=', '0')->where('location_id', $location_id)->get()->keyBy('vid');

        $results = [];

        $create_vlans = ($request->input('create-if-not-exists') == "on") ? true : false;
        $rename_vlans = ($request->input('overwrite-vlan-name') == "on") ? true : false;

        $testmode = ($request->input('test-mode') == "on") ? true : false;

        $start = microtime(true);

        foreach ($devices as $device) {
            if (!in_array($device->type, array_keys(self::$types))) {
                continue;
            }

            $current_vlans = $device->vlans()->get()->keyBy('vlan_id')->toArray();

            $results[$device->id] = [];

            $class = self::$types[$device->type];
            $results[$device->id] = $class::syncVlans($syncable_vlans, $current_vlans, $device, $create_vlans, $rename_vlans, $testmode);
        }

        $elapsed = microtime(true) - $start;

        return view('vlan.view_sync-results', compact('devices', 'results', 'elapsed', 'create_vlans', 'rename_vlans', 'testmode', 'location_id'));
    }

    static function syncVlansToDevice(Device $device, Request $request)
    {
        $devices = Device::all()->keyBy('id');
        $location_id = $device->location_id;
        $current_vlans = $device->vlans()->get()->keyBy('vlan_id')->toArray();
        $syncable_vlans = Vlan::where('is_synced', '!=', '0')->where('location_id', $device->location_id)->get()->keyBy('vid')->toArray();

        $results = [];

        $create_vlans = ($request->input('create-if-not-exists') == "on") ? true : false;
        $rename_vlans = ($request->input('overwrite-vlan-name') == "on") ? true : false;
        $testmode = ($request->input('test-mode') == "on") ? true : false;

        $start = microtime(true);

        $results[$device->id] = [];
        $class = self::$types[$device->type];
        $results[$device->id] = $class::syncVlans($syncable_vlans, $current_vlans, $device, $create_vlans, $rename_vlans, $testmode);

        $elapsed = microtime(true) - $start;

        return view('vlan.view_sync-results', compact('devices', 'results', 'elapsed', 'testmode', 'create_vlans', 'rename_vlans', 'location_id'));
    }

    static function updatePortVlans(String $cookie, DevicePort $port, $device_id, $untaggedVlan, $taggedVlans, $untaggedIsUpdated, $taggedIsUpdated) {

        $device = Device::find($device_id);

        $class = self::$types[$device->type];

        $login_info = $cookie;

        $vlans = $device->vlans()->get()->keyBy('id')->toArray();

        if(!$login_info) {
            return false;
        }

        $return = [];
        if($untaggedIsUpdated) {
            $success_untagged = $class::setUntaggedVlanToPort($untaggedVlan, $port, $device, $vlans, false, $login_info);
            $return['untagged'] = $success_untagged;
        }

        if($taggedIsUpdated) {
            $success_tagged = $class::setTaggedVlansToPort($taggedVlans, $port, $device, $vlans, false, $login_info);
            $return['tagged'] = $success_tagged;
        }

        list($cookie, $api_version) = explode(";", $login_info);
        $class::API_LOGOUT($device, $cookie, $api_version);

        return $return;
    }

    // Prevent mass login on switch api, use global cookie instead
    // Execute way faster
    public function startApiSession($id, Request $request) {

        $device = Device::find($id);

        if(!$device) {
            return json_encode(['success' => 'false', 'message' => __('DeviceNotFound')]);
        }

        $class = self::$types[$device->type];

        $login_info = $class::API_LOGIN($device);
        if(!$login_info) {
            return json_encode(['success' => 'false', 'message' => 'Failed login']);
        }

        return json_encode(['success' => 'true', 'hash' => Crypt::encrypt($login_info), 'timestamp' => time()]);	
    }

    static function closeApiSession($cookie, $id) {
        $device = Device::find($id);
        if(!$device) {
            return json_encode(['success' => 'false', 'message' => __('DeviceNotFound')]);
        }
        $class = self::$types[$device->type];
        list($cookie, $api_version) = explode(";", $cookie);
        $class::API_LOGOUT($device, $cookie, $api_version);
    }
}
