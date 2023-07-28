<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Helper\CLog;
use App\Models\Device;
use App\Models\DeviceBackup;
use App\Services\DeviceService;
use App\Services\PublicKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Helper\Diff;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
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
        if (!in_array($request->input('type'), array_keys(config('app.types')))) {
            return redirect()->back()->withErrors('Device type not found');
        }

        if (DeviceService::storeDevice($request)) {
            CLog::info("Switch", "Switch {$request->input('name')} created");
            return redirect()->back()->with('success', __('Msg.SwitchCreated'));
        }

        CLog::error("Switch", "Switch {$request->input('name')} could not be created");
        return redirect()->back()->withErrors('Could not create device');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function show($device_id)
    {
        $device = Device::with('ports', 'vlanports', 'uplinks', 'vlans', 'backups', 'clients', 'custom_uplink')->where('id', $device_id)->firstOrFail();
        
        // Sort ports
        $device->ports = $device->ports->sort(function ($a, $b) {
            return strnatcmp($a->name, $b->name);
        });

        // Get name for firmware model
        $device->type_name = config('app.typenames')[$device->type];

        // Get uplinks
        $found_uplinks = $device->uplinks->sort(function ($a, $b) {
            return strnatcmp($a->name, $b->name);
        })->groupBy('name');

        // Get port names for uplinks
        $generated_uplinks = [];
        foreach($found_uplinks as $key => $ports) {
            $name = $device->ports->where('name', $key)->first()->description;
            if ($name == '') {
                $name = $key;
            }

            $new_ports = array_map(function ($ports) use ($device) {
                return $device->ports->where('id', $ports['device_port_id'])->first()->name;
            }, $ports->toArray());

            // if key already exists, append new ports to existing ports
            if (array_key_exists($name, $generated_uplinks)) {
                $new_ports = array_merge(explode(",", $generated_uplinks[$name]), $new_ports);
            }

            $generated_uplinks[$name] = implode(",", $new_ports);

        }
        $found_uplinks = $generated_uplinks;

        // Get custom uplinks
        $custom_uplinks = $device->custom_uplink?->uplinks ?? '[]';
        $custom_uplinks_comma_seperated = implode(', ', json_decode($custom_uplinks, true));
        $custom_uplinks_array = json_decode($custom_uplinks) ?? [];


        // Sort vlans 
        $device->vlans = $device->vlans->sort(function ($a, $b) {
            return strnatcmp($a->vlan_id, $b->vlan_id);
        });

        // Get online status
        $is_online = DeviceService::isOnline($device->hostname);

        return view('switch.view_details', compact('device', 'is_online', 'found_uplinks', 'custom_uplinks_comma_seperated', 'custom_uplinks_array'));
    }

    public function showBackups(Device $device)
    {

        if (!$device) {
            return abort(404, __('DeviceNotFound'));
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

        $device = Device::find($request->input('id'));

        if(!$device) {
            return redirect()->back()->withErrors(['message' => 'Device not found']);
        }

        $tmp = $device->attributesToArray();

        if ($device->update($request->except('_token', '_method'))) {
            CLog::info("Switch", "Updated device {$device->name}", $device, Diff::compare($tmp, $device));
            return redirect()->back()->with('success', __('Msg.SwitchUpdated'));
        }


        CLog::error("Switch", "Failed to update device {$device->name}", $device, $device);
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
        $tmp = $find;

        if (!$find) {
            return redirect()->back()->with('message', __('DeviceNotFound'));
        }

        DeviceService::deleteDeviceData($find);

        if ($find->delete()) {
            CLog::info("Switch", "Switch {$tmp->name} deleted", $tmp, "ID: ".$tmp->id);

            return redirect()->back()->with('success', __('Msg.SwitchDeleted'));
        }
        return redirect()->back()->with('message', 'Could not delete device');
    }

    static function createBackup(Request $request)
    {
        $device = Device::find($request->device_id);

        if (!$device) {
            return json_encode(['success' => 'false', 'message' => __('DeviceNotFound')]);
        }

        if (!in_array($device->type, array_keys(config('app.types')))) {
            return json_encode(['success' => 'false', 'message' => 'Error creating backup']);
        }

        $class = config('app.types')[$device->type];
        $backup = $class::createBackup($device);

        if ($backup) {
            CLog::info("Switch", "Backup for switch {$device->name} created", $device, "ID: ".$device->id);

            return json_encode(['success' => 'true', 'message' => __('Msg.BackupCreated')]);
        } else {
            CLog::error("Switch", "Backup for switch {$device->name} could not be created", $device, "ID: ".$device->id);
            return json_encode(['success' => 'false', 'message' => 'Error creating backup']);
        }

        CLog::error("Switch", "Backup for switch {$device->name} could not be created", $device, "ID: ".$device->id);
        return json_encode(['success' => 'false', 'message' => __('DeviceNotFound') . $request->device_id]);
    }

    static function createBackupAllDevices()
    {
        $devices = Device::where('site_id', Auth::user()->currentSite()->id)->get()->keyBy('id');

        foreach ($devices as $device) {
            if (!in_array($device->type, array_keys(config('app.types')))) {
                continue;
            }

            $class = config('app.types')[$device->type];
            $class::createBackup($device);
        }

        CLog::info("Switch", "Backup for all switches created");
        return json_encode(['success' => 'true', 'message' => __('Msg.BackupCreated')]);
    }

    public function uploadPubkeysToSwitch(Device $device, Request $request)
    {
        if (!$device) {
            return json_encode(['success' => 'false', 'message' => __('DeviceNotFound')]);
        }

        $pubkeys = PublicKeyService::getPublicKeysAsArray();

        if (count($pubkeys) <= 2 || empty($pubkeys)) {
            CLog::error("Pubkey", "Not enough public keys to upload to switch {$device->name}", $device, $device->id);
            return json_encode(['success' => 'false', 'message' => __('Pubkeys.Sync.NotEnough')]);
        }

        if ($device) {
            $class = config('app.types')[$device->type];

            CLog::info("Pubkey", "Uploading public keys to switch {$device->name}", $device, $device->id);

            return $class::uploadPubkeys($device, $pubkeys);
        }

        CLog::error("Pubkey", "Could not upload public keys to switch {$device->name}", $device, $device->id);
        return json_encode(['success' => 'false', 'message' => __('DeviceNotFound')]);
    }

    static function uploadPubkeysAllDevices()
    {
        $pubkeys = PublicKeyService::getPublicKeysAsArray();

        $devices = Device::where('site_id', Auth::user()->currentSite()->id)->get()->keyBy('id');

        if (count($pubkeys) >= 2 && !empty($pubkeys)) {
            foreach ($devices as $device) {
                $class = config('app.types')[$device->type];

                CLog::info("Pubkey", "Uploading public keys to switch {$device->name}", $device, $device->id);

                $class::uploadPubkeys($device, $pubkeys);
            }

            CLog::info("Pubkey", "Uploading public keys to all switches");
            return json_encode(['success' => 'true', 'message' => __('Pubkeys.Sync.Success')]);
        }

        CLog::error("Pubkey", "Not enough public keys to upload to all switches");
        return json_encode(['success' => 'false', 'message' => __('Pubkeys.Sync.NotEnough')]);
    }

    static function restoreBackup(Request $request)
    {
        $device = Device::find($request->input('device-id'));
        $backup = DeviceBackup::find($request->input('id'));

        if ($device and $backup) {
            $password_switch = $request->input('password-switch');
            $class = config('app.types')[$device->type];
            $restore = $class::restoreBackup($device, $backup, $password_switch);

            if ($restore['success']) {
                CLog::info("Backup", "Backup for switch {$device->name} restored", $device, $device->id);
            } else {
                CLog::error("Backup", "Backup for switch {$device->name} could not be restored", $device, $device->id);
            }

            return ($restore['success']) ? redirect()->back()->with('success', __('Msg.BackupRestored')) : redirect()->back()->withErrors(['message' => $restore['data']]);
        }
    }
}
