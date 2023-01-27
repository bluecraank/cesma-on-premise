<?php

namespace App\Http\Controllers;

use App\Devices\ArubaCX;
use App\Devices\ArubaOS;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Models\Device;
use App\Models\Location;
use App\Models\Building;
use App\Models\DeviceBackup;
use App\Models\Room;
use App\Models\Vlan;
use App\Services\DeviceService;
use App\Services\PublicKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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
        $rooms = Room::all()->keyBy('id');
        
        $keys_list = PublicKeyService::getPubkeysDescriptionAsArray();
        $https = config('app.https');

        return view('switch.switch-overview', compact(
            'devices',
            'locations',
            'buildings',
            'rooms',
            'https',
            'keys_list'
        ));
    }

    static function view_uplinks()
    {
        $devices = Device::all()->keyBy('id');

        return view('switch.view_uplinks', compact('devices'));
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
            'building_id' => 'required|integer',
            'location_id' => 'required|integer',
            'location_desc' => 'required',
            'location_number' => 'required|integer',
            'type' => 'required|string'
        ])->validate();

        if (!in_array($request->input('type'), array_keys(self::$models))) {
            return redirect()->back()->withErrors('Device type not found');
        }

        if (DeviceService::storeDevice($request)) {
            return redirect()->back()->with('success', 'Device created');
        }

        return redirect()->back()->withErrors('Could not create device');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function show(Device $device)
    {
        $ports = $device->ports()->get();
        $portsById = $ports->keyBy('id');
        $portsByName = $ports->keyBy('name');
        $uplinks = $device->uplinks()->get()->keyBy('port_id');
        $is_online = DeviceService::isOnline($device->hostname);
        $uplinks = $device->uplinksGroupedKeyByNameArray();
        $vlans = $device->vlans()->get()->keyBy('id') ?? [];
        $backups = $device->backups()->latest()->take(15)->get() ?? [];
        $vlanPortsUntagged = $device->vlanPortsUntagged();
        $vlanPortsTagged = $device->vlanPortsTagged();

        return view('switch.view_details', compact('device', 'ports', 'uplinks', 'is_online', 'uplinks', 'vlans', 'backups', 'vlanPortsUntagged', 'vlanPortsTagged', 'portsById', 'portsByName'));
    }

    public function showBackups(Device $device)
    {

        if (!$device) {
            return abort(404, 'Device not found');
        }

        $backups = $device->backups()->latest()->take(150)->get();

        return view('switch.switch-backups', compact('device', 'backups'));
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
            $encrypted_pw = Crypt::encrypt($request->input('password'));
            $request->merge(['password' => $encrypted_pw]);
        } else {
            $request->merge(['password' => $device->whereId($request->input('id'))->first()->password]);
        }

        if ($device->whereId($request->input('id'))->update($request->except('_token', '_method'))) {
            LogController::log('Switch aktualisiert', '{"name": "' . $request->name . '", "id": "' . $request->id . '"}');

            return redirect()->back()->with('success', __('Msg.SwitchUpdated'));
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

        if (!$find) {
            return redirect()->back()->with('message', 'Device not found');
        }

        DeviceService::deleteDeviceData($find);

        if ($find->delete()) {
            LogController::log('Switch gelöscht', '{"name": "' . $find->name . '", "hostname": "' . $find->hostname . '"}');

            return redirect()->back()->with('success', __('Msg.SwitchDeleted'));
        }
        return redirect()->back()->with('message', 'Could not delete device');
    }

    static function createBackup(Request $request)
    {
        $device = Device::find($request->device_id);

        if (!$device) {
            return json_encode(['success' => 'false', 'message' => 'Device not found']);
        }

        if (!in_array($device->type, array_keys(self::$models))) {
            return json_encode(['success' => 'false', 'message' => 'Error creating backup']);
        }

        $class = self::$models[$device->type];
        $backup = $class::createBackup($device);

        if ($backup) {
            // LogController::log('Backup erstellt', '{"switch": "' .  $device->name . '"}');

            return json_encode(['success' => 'true', 'message' => 'Backup created']);
        } else {
            return json_encode(['success' => 'false', 'message' => 'Error creating backup']);
        }

        return json_encode(['success' => 'false', 'message' => 'Device not found' . $request->device_id]);
    }

    static function createBackupAllDevices()
    {
        $devices = Device::all()->keyBy('id');

        foreach ($devices as $device) {
            if (!in_array($device->type, array_keys(self::$models))) {
                continue;
            }

            $class = self::$models[$device->type];
            $class::createBackup($device);
        }

        return json_encode(['success' => 'true', 'message' => 'Backups created']);
    }

    public function uploadPubkeysToSwitch(Device $device, Request $request)
    {
        if (!$device) {
            return json_encode(['success' => 'false', 'message' => 'Device not found']);
        }

        $pubkeys = PublicKeyService::getPublicKeysAsArray();


        if ($device and count($pubkeys) >= 2 and !empty($pubkeys)) {
            $class = self::$models[$device->type];
            $class::uploadPubkeys($device, $pubkeys);

            return json_encode(['success' => 'true', 'message' => 'Pubkeys uploaded']);
        }

        return json_encode(['success' => 'false', 'message' => 'Error finding device or less than 2 pubkeys']);
    }

    static function uploadPubkeysAllDevices()
    {
        $pubkeys = PublicKeyService::getPublicKeysAsArray();

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
        $devices = Device::where('location', $locid)->where('id', 25)->get()->keyBy('id');
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
            return redirect()->back()->with('success', __('Msg.VlansSynced'));
        }
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
        $backup = DeviceBackup::find($request->input('id'));

        if ($device and $backup) {
            $password_switch = $request->input('password-switch');
            $class = self::$models[$device->type];
            $restore = $class::restoreBackup($device, $backup, $password_switch);

            LogController::log('Backupwiederherstellung', '{"switch": "' .  $device->name . '", "backup_datum": "' . $backup->created_at . '", "restored": "' . $restore['success'] . '"}');

            return ($restore['success']) ? redirect()->back()->with('success', __('Msg.BackupRestored')) : redirect()->back()->withErrors(['message' => $restore['data']]);
        }
    }
}
