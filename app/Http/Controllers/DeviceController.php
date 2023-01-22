<?php

namespace App\Http\Controllers;

use App\Devices\ArubaCX;
use App\Devices\ArubaOS;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Models\Device;
use App\Models\Location;
use App\Models\Building;
use App\Models\Backup;
use App\Http\Controllers\EncryptionController;
use App\Models\Client;
use App\Models\MacAddress;
use App\Models\Vlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    static $models = [
        'aruba-os' => ArubaOS::class,
        'aruba-cx' => ArubaCX::class,
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function index()
    {
        $devices = Device::all()->sortBy('name');
        $locations = Location::all()->keyBy('id');
        $buildings = Building::all()->keyBy('id');
        $keys_list = KeyController::getPubkeysDesc();
        $https = config('app.https', 'http://');

        return view('switch.switch-overview', compact(
            'devices',
            'locations',
            'buildings',
            'https',
            'keys_list'
        ));
    }

    static function view_trunks()
    {
        $data = self::getTrunksAllDevices();

        return view('switch.view_trunks', compact(
            'data'
        ));
    }

    static function view_uplinks()
    {
        $devices = Device::all()->keyBy('id');

        return view('switch.view_uplinks', compact('devices'));
    }

    static function view_details($id) {
        $device = Device::find($id);

        if (! $device) {
            return redirect()->back()->withErrors(['message' => 'Could not find device']);
        }
    
        $device->count_vlans = count(json_decode($device->vlan_data, true));
        $device->format_time = $device->updated_at->diffForHumans();
        $device->ports = json_decode($device->port_data, true);
        $device->count_trunks = count(array_filter($device->ports, function ($port) {
            return str_contains($port['id'], 'Trk');
        }));

        $device->ports_online = count(array_filter($device->ports, function ($port) {
            return $port['is_port_up'] === 'true' && !str_contains($port['id'], 'Trk');
        }));
        
        $device->count_ports = count($device->ports) - $device->count_trunks;
        $device->full_location = Location::find($device->location)->name . " - " . Building::find($device->building)->name . ", " . $device->details . " #" . $device->number;
        $backups = Backup::where('device_id', $id)->get()->sortByDesc('created_at')->take(15);
        $vlan_ports = json_decode($device->vlan_port_data);
        $port_statistic = json_decode($device->port_statistic_data, true);
        $system = json_decode($device->system_data);
        $vlans = json_decode($device->vlan_data, true);
        $clients = Client::where('switch_id', $id)->get()->groupBy('port_id');
    
        $trunks = $tagged = $untagged = [];
        foreach ($device->ports as $port) {
            if (str_contains($port['trunk_group'], 'Trk')) {
                $trunks[$port['trunk_group']][] = $port['id'];
                $untagged[$port['id']] = "Member of " . $port['trunk_group'];
            } else {
                $untagged[$port['id']] = "";
                $tagged[$port['id']] = [];
            }
        }

        // Count trunks and online ports
        $device->count_trunks = count($trunks);
        $ports_online = count(array_filter($device->ports, function($port) {
            return $port['is_port_up'] == "true" && !str_contains($port['id'], 'Trk');
        }));
    
        // Get tagged, untagged VLANs as list
        foreach ($vlan_ports as $vlan_port) {
            if (!$vlan_port->is_tagged) {
                $untagged[$vlan_port->port_id] = $vlan_port->vlan_id;
            } else {
                $tagged[$vlan_port->port_id][] = $vlan_port->vlan_id;
            }
        }

        foreach($device->ports as $port) {
            if(!str_contains($port['id'], 'Trk') && $port['trunk_group'] != null) {
                $tagged[$port['id']] = $tagged[$port['trunk_group']];
            }
        }
    
        // Onlinestatus
        $status = self::isOnline($device->hostname);
    
        return view('switch.view_details', compact(
            'device',
            'tagged',
            'untagged',
            'trunks',
            'port_statistic',
            'status',
            'system',
            'ports_online',
            'clients',
            'backups',
            'vlans'
        ));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreDeviceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDeviceRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:devices|max:100',
            'hostname' => 'required|unique:devices|max:100',
            'building' => 'required|integer',
            'location' => 'required|integer',
            'details' => 'required',
            'number' => 'required|integer',
        ])->validate();

        if (!in_array($request->input('type'), array_keys(self::$models))) {
            return redirect()->back()->withErrors('Device type not found');
        }

        // Get hostname from device else use ip
        $hostname = $request->input('hostname');
        if (filter_var($request->input('hostname'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $hostname = gethostbyaddr($request->input('hostname')) or $request->input('hostname');
        }
        $request->merge(['hostname' => $hostname]);


        // Encrypt password before store
        $encrypted_pw = EncryptionController::encrypt($request->all()['password']);
        $request->merge(['password' => $encrypted_pw]);

        // Create device
        $device = new Device();
        $device->hostname = $hostname;
        $device->password = $encrypted_pw;
        $device->type = $request->input('type');

        $class = self::$models[$request->input('type')];
        $device_data = $class::API_REQUEST_ALL_DATA($device);

        $noData = false;
        if (isset($device_data['success']) and $device_data['success'] == false) {
            $noData = true;
            $sys = [
                'name' => "Unknown",
                'model' => "Unknown",
                'serial' => "Unknown",
                'firmware' => "Unknown",
                'hardware' => "Unknown",
                'mac' => "000000000000",
            ];
            $device_data['sysstatus_data'] = $sys;
            $device_data['vlan_data'] = [];
            $device_data['ports_data'] = [];
            $device_data['portstats_data'] = [];
            $device_data['vlanport_data'] = [];
            $device_data['mac_table_data'] = [];
            $uplinks = json_encode([]);
        } else {
            // Get trunks only once
            // Assume that trunks are uplinks (most of the time)
            $device->port_data = json_encode($device_data['ports_data'], true);
            $trunks = $class::getDeviceTrunks($device);
            $uplinks = json_encode($trunks);
        }

        // Merge device data to request
        $request->merge([
            'mac_table_data' => json_encode($device_data['mac_table_data'], true),
            'vlan_data' => json_encode($device_data['vlan_data'], true),
            'port_data' => json_encode($device_data['ports_data']),
            'port_statistic_data' => json_encode($device_data['portstats_data'], true),
            'vlan_port_data' => json_encode($device_data['vlanport_data'], true),
            'system_data' => json_encode($device_data['sysstatus_data'], true),
            'uplinks' => $uplinks
        ]);

        if ($device = Device::create($request->all())) {
            LogController::log('Switch erstellt', '{"name": "' . $request->input('name') . '", "hostname": "' . $request->input('hostname') . '"}');

            if (!$noData) {
                VlanController::AddVlansFromDevice($device_data['vlan_data'], $request->input('name'), $request->input('location'));
                $class::createBackup($device);
            }

            return redirect()->back()->with('success', 'Device successfully created');
        }

        return redirect('/')->withErrors($validator);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function show(Device $device)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function edit(Device $device)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDeviceRequest  $request
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDeviceRequest $request, Device $device)
    {
        if ($request->input('password') != "__hidden__" and $request->input('password') != "") {
            $encrypted_pw = EncryptionController::encrypt($request->input('password'));
            $request->merge(['password' => $encrypted_pw]);
        } else {
            $request->merge(['password' => $device->whereId($request->input('id'))->first()->password]);
        }

        if ($device->whereId($request->input('id'))->update($request->except('_token', '_method'))) {
            LogController::log('Switch aktualisiert', '{"name": "' . $request->name . '", "id": "' . $request->id . '"}');

            return redirect()->back()->with('success', 'Device updated');
        }

        return redirect()->back()->withErrors(['message' => 'Could not update device']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $device)
    {
        $find = Device::find($device->input('id'));
        Backup::where('device_id', $find->id)->delete();
        Client::where('switch_id', $find->id)->delete();
        MacAddress::where('device_id', $find->id)->delete();
        if ($find->delete()) {
            LogController::log('Switch gelöscht', '{"name": "' . $find->name . '", "hostname": "' . $find->hostname . '"}');

            return redirect()->back()->with('success', 'Device deleted');
        }
        return redirect()->back()->with('message', 'Could not delete device');
    }

    static function refresh(Request $request)
    {
        $start = microtime(true);

        $device = Device::find($request->input('device_id'));

        $device_data = self::$models[$device->type]::API_REQUEST_ALL_DATA($device);

        if (isset($device_data['success']) and $device_data['success'] == false) {
            return json_encode(['success' => 'false', 'message' => 'Could not get data from device']);
        }

        MacAddressController::refreshMacDataFromSwitch($device->id, $device_data['mac_table_data'], $device->uplinks);

        $device->update([
            'mac_table_data' => json_encode($device_data['mac_table_data'], true),
            'vlan_data' => json_encode($device_data['vlan_data'], true),
            'port_data' => json_encode($device_data['ports_data'], true),
            'port_statistic_data' => json_encode($device_data['portstats_data'], true),
            'vlan_port_data' => json_encode($device_data['vlanport_data'], true),
            'system_data' => json_encode($device_data['sysstatus_data'], true)
        ]);

        $elapsed = microtime(true) - $start;
        return json_encode(['success' => 'true', 'message' => 'Refreshed device (' . number_format($elapsed, 2) . 's)']);
    }

    static function refreshAll()
    {
        $devices = Device::all()->keyBy('id');
        $time = microtime(true);

        foreach ($devices as $device) {
            $start = microtime(true);
            $device_data = self::$models[$device->type]::API_REQUEST_ALL_DATA($device);

            if (isset($device_data['success']) and $device_data['success'] == false) {
                echo "Error getting switch data: " . $device->name . "\n";
                continue;
            }

            MacAddressController::refreshMacDataFromSwitch($device->id, $device_data['mac_table_data'], $device->uplinks);


            $device->update([
                'mac_table_data' => json_encode($device_data['mac_table_data'], true),
                'vlan_data' => json_encode($device_data['vlan_data'], true),
                'port_data' => json_encode($device_data['ports_data'], true),
                'port_statistic_data' => json_encode($device_data['portstats_data'], true),
                'vlan_port_data' => json_encode($device_data['vlanport_data'], true),
                'system_data' => json_encode($device_data['sysstatus_data'], true)
            ]);

            $elapsed = number_format(microtime(true) - $start, 2);
            echo "Refreshed switch: " . $device->name . " (" . $elapsed . "sec)\n";
        }

        echo "Took " . number_format(microtime(true) - $time, 2) . " seconds\n";
    }

    static function createBackup(Request $request)
    {
        $id = $request->input('device_id');
        $device = Device::find($id);
        if ($device) {
            if (!in_array($device->type, array_keys(self::$models))) {
                return json_encode(['success' => 'false', 'message' => 'Error creating backup']);
            }

            $class = self::$models[$device->type];
            $backup = $class::createBackup($device);

            if ($backup) {
                LogController::log('Backup erstellt', '{"switch": "' .  $device->name . '"}');

                return json_encode(['success' => 'true', 'message' => 'Backup created']);
            } else {
                return json_encode(['success' => 'false', 'message' => 'Error creating backup']);
            }
        }

        return json_encode(['success' => 'false', 'message' => 'Device not found' . $id]);
    }

    static function createBackupAllDevices()
    {
        $device = Device::all()->keyBy('id');

        foreach ($device as $switch) {
            if (!in_array($switch->type, array_keys(self::$models))) {
                continue;
            }

            $class = self::$models[$switch->type];
            $class::createBackup($switch);
        }

        return json_encode(['success' => 'true', 'message' => 'Backups created']);
    }

    static function getTrunksAllDevices()
    {
        $devices = Device::all();

        $data = [];
        foreach ($devices as $device) {
            if (!in_array($device->type, array_keys(self::$models))) {
                continue;
            }

            $class = self::$models[$device->type];
            $data[$device->id] = array(
                'name' => $device->name,
                'trunks' => $class::getDeviceTrunks($device),
            );
        }

        return $data;
    }

    public function uploadPubkeysToSwitch($id, Request $request)
    {
        $device = Device::find($id);
        $pubkeys = KeyController::getPubkeysAsArray();


        if ($device and count($pubkeys) >= 2 and !empty($pubkeys)) {
            $class = self::$models[$device->type];
            $class::uploadPubkeys($device, $pubkeys);

            return json_encode(['success' => 'true', 'message' => 'Pubkeys uploaded']);
        }

        return json_encode(['success' => 'false', 'message' => 'Error finding device or less than 2 pubkeys']);
    }

    static function uploadPubkeysAllDevices()
    {
        $pubkeys = KeyController::getPubkeysAsArray();

        $devices = Device::all()->keyBy('id');

        if (count($pubkeys) >= 2 and !empty($pubkeys)) {
            foreach ($devices as $device) {
                $class = self::$models[$device->type];
                $class::uploadPubkeys($device, $pubkeys);
            }

            return json_encode(['success' => 'true', 'message' => 'Pubkeys uploaded to all devices']);
        }

        return json_encode(['success' => 'false', 'message' => 'Not enough pubkeys']);
    }

    static function updateVlansAllDevices(Request $request)
    {
        $locid = $request->input('location_id');
        $devices = Device::where('location', $locid)->get()->keyBy('id');
        $vlans = Vlan::where('sync', '!=', '0')->where('location_id', $locid)->get()->keyBy('vid');
        $results = [];

        $create_vlan = ($request->input('create-if-not-exists') == "on") ? true : false;
        $overwrite_name = ($request->input('overwrite-vlan-name') == "on") ? true : false;

        $test = ($request->input('test-mode') == "on") ? true : false;

        $start = microtime(true);
        foreach ($devices as $device) {
            if (!in_array($device->type, array_keys(self::$models))) {
                continue;
            }

            $vlans_switch = json_decode($device->vlan_data, true);

            $results[$device->id] = [];

            $class = self::$models[$device->type];
            $results[$device->id] = $class::syncVlans($vlans, $vlans_switch, $device, $create_vlan, $overwrite_name, $test);
        }

        $elapsed = microtime(true) - $start;

        if ($request->input('show-results') == "on") {
            return view('vlan.view_sync-results', compact('devices', 'results', 'elapsed'));
        } else {
            return redirect()->back()->with('success', 'Vlans synced');
        }
    }

    static function updateUplinks(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ])->validate();

        $device = Device::find($request->input('id'));

        if (str_contains($request->input('uplinks'), ":") or str_contains($request->input('uplinks'), " ") or str_contains($request->input('uplinks'), ";")) {
            return redirect()->back()->withErrors(['uplinks' => 'Uplinks must be comma separated']);
        }

        if ($device) {
            $uplinks = $request->input('uplinks');
            $uplinks = explode(',', $uplinks);
            $device->uplinks = json_encode($uplinks);
            $device->save();
            LogController::log('Uplinks aktualisiert', '{"switch": "' .  $device->name . '", "uplinks": "' . $request->input('uplinks') . '"}');
        }

        return redirect()->back();
    }

    static function setUntaggedVlanToPort(Request $request)
    {

        $device = Device::find($request->input('device'));

        if ($device) {
            $vlans = json_decode($request->input('vlans'), true);
            $ports = json_decode($request->input('ports'), true);

            if (is_array($vlans) and is_array($ports) and count($vlans) != 0 and count($vlans) == count($ports)) {
                $class = self::$models[$device->type];

                return $class::setUntaggedVlanToPort($vlans, $ports, $device);
            } else {
                return json_encode(['success' => 'false', 'message' => 'No changes found']);
            }

            return json_encode(['success' => 'false', 'message' => 'Invalid data retrieved']);
        }
        return json_encode(['success' => 'false', 'message' => 'Device not found']);
    }

    static function setTaggedVlanToPort(Request $request)
    {
        $device_id = $request->input('device');

        if ($device = Device::find($device_id)) {
            $vlans = json_decode($request->input('vlans'), true);
            $port = $request->input('port');


            $success_c = 0;
            $failed_c = 0;
            $ins = 0;

            $class = self::$models[$device->type];
            $return = ['message' => ''];
            $result = $class::setTaggedVlanToPort($vlans, $port, $device);
            foreach ($result as $success) {
                $return['message'] .= "<br>" . $success['message'];
                if ($success['success'] == false) {
                    $failed_c++;
                } else {
                    $success_c++;
                }
                $ins++;
            }

            return json_encode(['success' => 'true', 'message' => "Updated " . $success_c . " of " . $ins . " vlans on port " . $port . $return['message']]);
        }
        return json_encode(['success' => 'false', 'message' => 'Invalid data retrieved']);
    }

    static function restoreBackup(Request $request)
    {
        $device = Device::find($request->input('device-id'));
        $backup = Backup::find($request->input('id'));

        if ($device and $backup) {
            $password_switch = $request->input('password-switch');
            $class = self::$models[$device->type];
            $restore = $class::restoreBackup($device, $backup, $password_switch);

            LogController::log('Backupwiederherstellung', '{"switch": "' .  $device->name . '", "backup_datum": "' . $backup->created_at . '", "restored": "' . $restore['success'] . '"}');

            return ($restore['success']) ? redirect()->back()->with('success', 'Backup restored') : redirect()->back()->withErrors(['message' => $restore['data']]);
        }
    }

    static function isOnline($hostname)
    {
        try {
            if ($fp = fsockopen($hostname, 22, $errCode, $errStr, 0.2)) {
                fclose($fp);
                return "has-text-success";
            }
            fclose($fp);

            return "has-text-danger";
        } catch (\Exception $e) {
            return "has-text-danger";
        }
    }

    static function storeStats($data, $device)
    {

    }
}
