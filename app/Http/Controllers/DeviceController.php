<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceRequest;
use App\Helper\CLog;
use App\Models\Device;
use App\Models\DeviceBackup;
use App\Models\DeviceVlan;
use App\Services\PublicKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
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
            CLog::info("Switch", "Switch {$request->input('name')} created");
            return redirect()->back()->with('success', __('Successfully created device'));
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
        $device_id = $device->id;
        $device = Device::with('ports', 'vlanports', 'uplinks', 'vlans', 'backups', 'clients')->where('id', $device_id)->firstOrFail();

        // Sort ports
        $device->ports = $device->ports->sort(function ($a, $b) {
            return strnatcmp($a->name, $b->name);
        });

        // Get name for firmware model
        $device->type_name = config('app.typenames')[$device->type];

        // Get uplinks
        $found_uplinks = $device->uplinks->sort(function ($a, $b) {
            return strnatcmp($a->name, $b->name);
        })->groupBy('name')->toArray();

        // Sort vlans
        $device->vlans = $device->vlans->sort(function ($a, $b) {
            return strnatcmp($a->vlan_id, $b->vlan_id);
        });

        $enoughPubkeysToSync = count(PublicKeyService::getPublicKeysAsArray());

        return view('device.show', compact('device', 'found_uplinks', 'enoughPubkeysToSync'));
    }

    public function showBackups(Device $device)
    {

        if (!$device) {
            return abort(404, 'Device not found');
        }

        $backups = $device->backups()->latest()->take(150)->get();

        return view('device.backups', compact('device', 'backups'));
    }

    static function createBackup(Device $device)
    {
        if (!$device) {
            return json_encode(['success' => 'false', 'message' => __('Device could not be found')]);
        }

        if (!in_array($device->type, array_keys(config('app.types')))) {
            return json_encode(['success' => 'false', 'message' => __('Error creating backup')]);
        }

        $class = config('app.types')[$device->type];
        $backup = $class::createBackup($device);

        if ($backup) {
            CLog::info("Switch", "Backup for switch {$device->name} created", $device, "ID: ".$device->id);

            return json_encode(['success' => 'true', 'message' => __('Backup successfully created')]);
        } else {
            CLog::error("Switch", "Backup for switch {$device->name} could not be created", $device, "ID: ".$device->id);
            return json_encode(['success' => 'false', 'message' => __('Error creating backup')]);
        }
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
        return json_encode(['success' => 'true', 'message' => __('Successfully created backups')]);
    }

    public function syncPubkeys(Device $device)
    {
        if (!$device) {
            return json_encode(['success' => 'false', 'message' => __('Device could not be found')]);
        }

        $pubkeys = PublicKeyService::getPublicKeysAsArray();

        if (count($pubkeys) <= 2 || empty($pubkeys)) {
            CLog::error("Pubkey", "Not enough public keys to upload to switch {$device->name}", $device, $device->id);
            return json_encode(['success' => 'false', 'message' => __('Not enough public keys to upload to switch')]);
        }

        $class = config('app.types')[$device->type];
        CLog::info("Pubkey", "Uploading public keys to switch {$device->name}", $device, $device->id);
        return $class::uploadPubkeys($device, $pubkeys);
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

    static function syncVlansToDevice(Request $request) {
        $device = Device::find($request->input('device'));

        $vlans = $request->input('vlans') ?? '{}';
        $vlans = json_decode($vlans, true);

        $create = $request->input('createVlans') ?? false;
        $rename = $request->input('renameVlans') ?? false;
        $tagToUplink = $request->input('tagToUplink') ?? false;
        $testmode = $request->input('testmode') == "false" ? false : true;

        // // For security!
        // $testmode = true;

        if ($device and $vlans) {
            $class = config('app.types')[$device->type];
            $sync = $class::syncVlans($vlans, $device, $create, $rename, $tagToUplink, $testmode);

            CLog::info("Vlan", "Vlans for switch {$device->name} synced", $device, $device->id);
            return json_encode($sync);
        }

        return json_encode(['success' => 'false', 'message' => __('Device could not be found')]);
    }
}
