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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function Termwind\render;

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
    function index() {
        $devices = Device::all()->sortBy('name');
        $locations = Location::all()->keyBy('id');
        $buildings = Building::all()->keyBy('id');
        $keys = KeyController::getPubkeysDesc();
        $https = config('app.https', 'http://');

        return view('switch.index_devices', compact(
            'devices',
            'locations',
            'buildings',
            'https',
            'keys'
        ));
    }

    static function index_trunks() {
        $data = self::getTrunksAllDevices();

        return view('switch.trunks', compact(
            'data'
        ));
    }

    static function index_uplinks() {
        $devices = Device::all()->keyBy('id');
        
        return view('switch.uplinks', compact('devices'));
    }

    function index_live($id) {
        $device = Device::find($id);

        if ($device) {
            $trunks = [];
            $tagged = [];
            $untagged = [];
            $ports_online = 0;
            $count_ports = 0;
            $device->count_trunks = 0;

            $backups = Backup::where('device_id', $id)->get()->sortByDesc('created_at')->take(15);
            $device->count_vlans = count(json_decode($device->vlan_data, true));
            $device->format_time = $device->updated_at->diffForHumans();
            $device->ports = json_decode($device->port_data, true);
            $vlan_ports = json_decode($device->vlan_port_data);
            $port_statistic_raw = json_decode($device->port_statistic_data, true);
            $system = json_decode($device->system_data);
            $vlans = json_decode($device->vlan_data, true);

            $device->full_location = Location::find($device->location)->name . " - " . Building::find($device->building)->name . ", " . $device->details . " #" . $device->number;

            // Correct port_statistic array
            $port_statistic = [];
            foreach ($port_statistic_raw as $key => $value) {
                $port_statistic[$value['id']] = $value;
                unset($port_statistic_raw[$key]);
            }

            // Sort and count ports
            foreach ($device->ports as $port) {
                $untagged[$port['id']] = "";
                $tagged[$port['id']] = [];

                if (str_contains($port['id'], 'Trk')) {
                    $device->count_trunks++;
                } else {
                    $count_ports++;
                }

                if ($port['trunk_group'] != "") {
                    $trunks[$port['trunk_group']][] = $port['id'];
                    $untagged[$port['id']] = "Member of " . $port['trunk_group'];
                }

                if($port['is_port_up'] == "true" and !str_contains($port['id'], 'Trk')) {
                    $ports_online++;
                } 

            }
            
            // Get tagged, untagged VLANs as list
            foreach ($vlan_ports as $vlan_port) {
                if (!$vlan_port->is_tagged and !str_contains($vlan_port->port_id, "Trk")) {
                    $untagged[$vlan_port->port_id] = $vlan_port->vlan_id;
                } elseif ($vlan_port->is_tagged and !str_contains($vlan_port->port_id, "Trk")) {
                    $tagged[$vlan_port->port_id][] = $vlan_port->vlan_id;
                } elseif ($vlan_port->is_tagged and str_contains($vlan_port->port_id, "Trk")) {
                    $tagged[$vlan_port->port_id][] = $vlan_port->vlan_id;
                }
            }

            // Onlinestatus
            $status = $this->isOnline($device->hostname);

            return view('switch.live', compact(
                'device',
                'tagged',
                'untagged',
                'trunks',
                'port_statistic',
                'status',
                'system',
                'ports_online',
                'count_ports',
                'backups',
                'vlans'
            ));
        }

        return redirect()->back()->withErrors(['error' => 'Could not find device']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreDeviceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDeviceRequest $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:devices|max:100',
            'hostname' => 'required|unique:devices|max:100',
            'building' => 'required|integer',
            'location' => 'required|integer',
            'details' => 'required',
            'number' => 'required|integer',
        ])->validate();

        // Get hostname from device else use ip
        $hostname = $request->input('hostname');
        if (filter_var($request->input('hostname'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $hostname = gethostbyaddr($request->input('hostname')) or $request->input('hostname');
        }

        // Encrypt password before store
        $encrypted_pw = EncryptionController::encrypt($request->all()['password']);
        $request->merge(['password' => $encrypted_pw]);
        $request->merge(['hostname' => $hostname]);

        $device = new Device();
        $device->hostname = $hostname;
        $device->password = $encrypted_pw;
        $device->type = $request->input('type');
        $device->uplinks = [];
        if($request->input('uplinks_ports') and !empty($request->input('uplinks_ports'))) {
            $device->uplinks = json_encode(explode(",", $request->input('uplinks')));
        }
        
        if(!in_array($device->type, array_keys(self::$models))) {
            return redirect()->back()->withErrors('Device type not found');
        }

        $class = self::$models[$request->input('type')]; 
        $device_data = $class::getApiData($device);

        
        $noData = false;
        if(isset($device_data['success']) and $device_data['success'] == false) {
            $noData = true;
            $sys = [
                'name' => "AOS-UNKNOWN",
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
        }

        // Merge device data to request
        $request->merge([
            'mac_table_data' => json_encode($device_data['mac_table_data'], true), 
            'vlan_data' => json_encode($device_data['vlan_data'], true), 
            'port_data' => json_encode($device_data['ports_data'], true), 
            'port_statistic_data' => json_encode($device_data['portstats_data'], true), 
            'vlan_port_data' => json_encode($device_data['vlanport_data'], true), 
            'system_data' => json_encode($device_data['sysstatus_data'], true)
        ]);

        if ($validator and $device = Device::create($request->all())) {
            LogController::log('Switch erstellt', '{"name": "' . $request->input('name') . '", "hostname": "' . $request->input('hostname') . '"}');
            if(!$noData) {
                VlanController::AddVlansFromDevice($device_data['vlan_data'], $request->input('name'), $request->input('location'));
                $class::createBackup($device);
            }
            return redirect()->back()->with('success', 'Device added');
        }

        return redirect('/')->withErrors($validator);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function show(Device $device) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function edit(Device $device) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDeviceRequest  $request
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDeviceRequest $request, Device $device) {
        if ($request->input('password') != "__hidden__" and $request->input('password') != "") {
            $encrypted_pw = EncryptionController::encrypt($request->input('password'));
            $request->merge(['password' => $encrypted_pw]);
        } else {	
            $request->merge(['password' => $device->whereId($request->input('id'))->first()->password]);
        }

        if($request->input('uplinks') and !empty($request->input('uplinks'))) {
            $request->merge(['uplinks' => json_encode(explode(",", $request->input('uplinks')))]);
        }

        if ($device->whereId($request->input('id'))->update($request->except('_token', '_method'))) {
            LogController::log('Switch aktualisiert', '{"name": "' . $request->name . '", "id": "' . $request->id . '"}');

            return redirect()->back()->with('success', 'Device updated');
        }

        return redirect()->back()->withErrors(['error' => 'Could not update device']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $device) {
        $find = Device::find($device->input('id'));
        Backup::where('device_id', $find->id)->delete();
        Client::where('switch_id', $find->id)->delete();
        if ($find->delete()) {
            LogController::log('Switch gelöscht', '{"name": "' . $find->name . '", "hostname": "' . $find->hostname . '"}');

            return redirect()->back()->with('success', 'Device deleted');
        }
        return redirect()->back()->with('error', 'Could not delete device');
    }

    static function refresh(Request $request) {
        $device = Device::find($request->input('id'));


        if(!in_array($device->type, array_keys(self::$models))) {
            return json_encode(['success' => 'false', 'error' => 'Could not update device']);
        }

        $class = self::$models[$device->type]; 
        $device_data = $class::getApiData($device);
        
        if(isset($device_data['success']) and $device_data['success'] == false) {
            return json_encode(['success' => 'false', 'error' => "No data received from device"]);
        }

        if($device->update(
            ['mac_table_data' => json_encode($device_data['mac_table_data'], true), 
            'vlan_data' => json_encode($device_data['vlan_data'], true), 
            'port_data' => json_encode($device_data['ports_data'], true), 
            'port_statistic_data' => json_encode($device_data['portstats_data'], true), 
            'vlan_port_data' => json_encode($device_data['vlanport_data'], true), 
            'system_data' => json_encode($device_data['sysstatus_data'], true)])) 
        {
            LogController::log('Switch aktualisiert', '{"name": "' . $device->name . '", "hostname": "' . $device->hostname . '"}');
            return json_encode(['success' => 'true']);
        }

        return json_encode(['success' => 'false', 'error' => 'Could not update device']);
    }

    static function refreshAll() {
        $devices = Device::all()->keyBy('id');

        $time = microtime(true);

        foreach($devices as $device) {
            $start = microtime(true); 
            $device_data = self::$models[$device->type]::getApiData($device);
            
            if(isset($device_data['success']) and $device_data['success'] == false) {
                continue;
            }

            MacAddress::where("device_id", $device->id)->delete();
            foreach($device_data['mac_table_data'] as $key => $mac) {
                if($device->uplinks == null) {
                    $uplinks = $device->uplinks = [];
                } else {
                    $uplinks = json_decode($device->uplinks, true);
                }

                if(!in_array($mac['port'], $uplinks) and !str_contains($mac['port'], "Trk")) {
                    MacAddressController::store($mac['mac'], $mac['port'], $mac['vlan'], $device->id);
                }            
            }

            if(isset($device_data) and $device->update(
                ['mac_table_data' => json_encode($device_data['mac_table_data'], true), 
                'vlan_data' => json_encode($device_data['vlan_data'], true), 
                'port_data' => json_encode($device_data['ports_data'], true), 
                'port_statistic_data' => json_encode($device_data['portstats_data'], true), 
                'vlan_port_data' => json_encode($device_data['vlanport_data'], true), 
                'system_data' => json_encode($device_data['sysstatus_data'], true)])) 
            {
                $elapsed = microtime(true)-$start;
                echo "Got switch data: " . $device->name." (".$elapsed."sec)\n";
            } else {
                echo "Error getting switch data: "
                 . $device->name."\n";
            }
        }

        echo "Took ".microtime(true)-$time." seconds\n";
    }

    static function createBackup() {
        $id = request()->input('id');
        $device = Device::find($id);
        if($device) {
            if(!in_array($device->type, array_keys(self::$models))) {
                return json_encode(['success' => 'false', 'error' => 'Error creating backup']);
            }

            $class = self::$models[$device->type];
            $backup = $class::createBackup($device);

            if($backup) {
                return json_encode(['success' => 'true', 'error' => 'Backup created']);
            } else {
                return json_encode(['success' => 'false', 'error' => 'Error creating backup']);
            }
        }

        return json_encode(['success' => 'false', 'error' => 'Error creating backup']);
    }

    static function createBackupAllDevices() {
        $device = Device::all()->keyBy('id');

        foreach($device as $switch) {
            if(!in_array($switch->type, array_keys(self::$models))) {
                continue;
            }

            $class = self::$models[$switch->type];
            $class::createBackup($switch);
        }

        return json_encode(['success' => 'true', 'error' => 'Backups created']);
    }

    static function getClientsAllDevices() {
        $device = Device::all()->keyBy('id');

        foreach($device as $switch) {
            if(!in_array($switch->type, array_keys(self::$models))) {
                continue;
            }

            $class = self::$models[$switch->type];
            $class::getClients($switch);
        }

        return json_encode(['success' => 'true', 'error' => 'Clients updated']);

    }

    static function getTrunksAllDevices() {
        $devices = Device::all();

        $data = [];
        foreach($devices as $device) {
            if(!in_array($device->type, array_keys(self::$models))) {
                continue;
            }
    
            $class = self::$models[$device->type]; 
            $data[$device->id] = array(
                'name' => $device->name,
                'trunks' => $class::getTrunks($device),
            );
        }

        return $data;
    }

    public function uploadPubkeysToSwitch($id, Request $request) {
        $device = Device::find($id);

        if($device) {
            $class = self::$models[$device->type]; 
            $pubkeys = KeyController::getPubkeysAsArray();
            return $class::uploadPubkeys($device, $pubkeys);
        }

        return json_encode(['success' => 'false', 'error' => 'Error finding device']);
    }

    static function uploadPubkeysAllDevices() {
        $pubkeys = KeyController::getPubkeysAsArray();

        $devices = Device::all()->keyBy('id');

        if(count($pubkeys) >= 2 and !empty($pubkeys)) {
            foreach($devices as $device) {
                $class = self::$models[$device->type];
                $class::uploadPubkeys($device, $pubkeys);
            }

            return json_encode(['success' => 'true', 'error' => 'Pubkeys uploaded']);
        }

        return json_encode(['success' => 'false', 'error' => 'No pubkeys found']);
    }

    static function updateVlansAllDevices(Request $request) {
        $devices = Device::all()->keyBy('id');
        $vlans = Vlan::where('sync', '!=', '0')->get()->keyBy('vid');
        $results = [];

        $create_vlan = ($request->input('create-if-not-exists') == "on") ? true : false;
        $test = ($request->input('test-mode') == "on") ? true : false;

        $start = microtime(true);
        foreach($devices as $device) {
            if(!in_array($device->type, array_keys(self::$models))) {
                continue;
            }

            $vlans_switch = json_decode($device->vlan_data, true);

            $results[$device->id] = [];

            $class = self::$models[$device->type];
            $results[$device->id] = $class::updateVlans($vlans, $vlans_switch, $device, $create_vlan, $test);
        }

        $elapsed = microtime(true)-$start;

        if($request->input('show-results') == "on") {
        return view('vlan.sync', compact('devices', 'results', 'elapsed'));
        } else {
            return redirect()->back()->with('success', 'Vlans synced');
        }
    }

    static function updateUplinks(Request $request) {

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ])->validate();

        $device = Device::find($request->input('id'));

        if(str_contains($request->input('uplinks'), ":") or str_contains($request->input('uplinks'), " ") or str_contains($request->input('uplinks'), ";")) {
            return redirect()->back()->withErrors(['uplinks' => 'Uplinks must be comma separated']);
        }

        if($device) {
            $uplinks = $request->input('uplinks');
            $uplinks = explode(',', $uplinks);
            $device->uplinks = json_encode($uplinks);
            $device->save();
        }

        return redirect()->back();
    }

    static function updateUntaggedPorts(Request $request) {

        $device = Device::find($request->input('device'));

        if($device) {
            $vlans = json_decode($request->input('vlans'), true);
            $ports = json_decode($request->input('ports'), true);

            if(is_array($vlans) and is_array($ports) and count($vlans) != 0 and count($vlans) == count($ports)) {
                $class = self::$models[$device->type];
                return $class::updatePortVlanUntagged($vlans, $ports, $device);
            } else {
                return json_encode(['success' => 'false', 'error' => 'No changes found']);
            }

            return json_encode(['success' => 'false', 'error' => 'Invalid data retrieved']);
        }
        return json_encode(['success' => 'false', 'error' => 'Device not found']);
    }

    static function restoreBackup(Request $request) {
        $device = Device::find($request->input('device-id'));
        $backup = Backup::find($request->input('id'));

        if($device and $backup) {
            $password = $request->input('password');
            $password_switch = $request->input('password-switch');
            if(Hash::check($password, Auth::user()->password)) {
                $class = self::$models[$device->type];
                $restore = $class::restoreBackup($device, $backup, $password_switch);
                return ($restore['success']) ? redirect()->back()->with('success', 'Backup restored') : redirect()->back()->withErrors(['error' => $restore['data']]);
            } else {
                return json_encode(['success' => 'false', 'error' => 'Your password is wrong']);
            }
        }
    }

    static function isOnline($hostname) {
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

}
