<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Models\Device;
use App\Models\Location;
use App\Models\Building;
use App\Models\Backup;
use App\Http\Controllers\EncryptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\ErrorHandler\Debug;


class DeviceController extends Controller
{
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
        $https = config('app.https', 'http://');

        return view('device.overview', compact(
            'devices',
            'locations',
            'buildings',
            'https'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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

        // Get hostname from device else use ip
        $hostname = $request->input('hostname');
        if (filter_var($request->input('hostname'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $hostname = gethostbyaddr($request->input('hostname')) or $request->input('hostname');
        }

        // Encrypt password before store
        $encrypted_pw = EncryptionController::encrypt($request->all()['password']);
        $request->merge(['password' => $encrypted_pw]);
        $request->merge(['hostname' => $hostname]);

        // Get login cookie
        if (!$auth_cookie = ApiRequestController::login($encrypted_pw, $hostname)) {
            return redirect()->back()->withErrors(['error' => 'Could not login to device']);
            //return false;
        }

        // Get data from device
        if (!$device_data = ApiRequestController::getData($auth_cookie, $hostname)) {
            ApiRequestController::logout($auth_cookie, $hostname);
            return redirect()->back()->withErrors(['error' => 'Could not get data from device']);
            //return false;
        }

        // Merge data from device with requests
        ApiRequestController::logout($auth_cookie, $hostname);
        $request->merge(['vlan_data' => json_encode($device_data['vlan_data'], true)]);
        $request->merge(['port_data' => json_encode($device_data['ports_data'], true)]);
        $request->merge(['port_statistic_data' => json_encode($device_data['portstats_data'], true)]);
        $request->merge(['vlan_port_data' => json_encode($device_data['vlanport_data'], true)]);
        $request->merge(['system_data' => json_encode($device_data['sysstatus_data'], true)]);

        if ($validator and Device::create($request->all())) {
            LogController::log('Switch erstellt', '{"name": "' . $request->input('name') . '", "hostname": "' . $request->input('hostname') . '"}');
            VlanController::AddVlansFromDevice($device_data['vlan_data'], $request->input('name'), $request->input('location'));
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

        return redirect()->back()->withErrors(['error' => 'Could not update device']);
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
        if ($find->delete()) {
            LogController::log('Switch gelöscht', '{"name": "' . $find->name . '", "hostname": "' . $find->hostname . '"}');

            return redirect()->back()->with('success', 'Device deleted');
        }
        return redirect()->back()->with('error', 'Could not delete device');
    }

    function trunks()
    {
        $devices = Device::all();

        return view('device.trunks', compact(
            'devices',
        ));
    }

    function refresh(Request $request)
    {
        $device = Device::find($request->input('id'));

        // Get login cookie

        if (!$auth_cookie = ApiRequestController::login($device->password, $device->hostname)) {
            return json_encode(['success' => 'false', 'error' => 'Could not login to device']);
            //return false;
        }

        // Get data from device
        if (!$device_data = ApiRequestController::getData($auth_cookie, $device->hostname)) {
            ApiRequestController::logout($auth_cookie, $device->hostname);
            return json_encode(['success' => 'false', 'error' => 'Could not get data from device']);
        }

        ApiRequestController::logout($auth_cookie, $device->hostname);
        if(Device::whereId($request->input('id'))->update(['vlan_data' => json_encode($device_data['vlan_data'], true), 'port_data' => json_encode($device_data['ports_data'], true), 'port_statistic_data' => json_encode($device_data['portstats_data'], true), 'vlan_port_data' => json_encode($device_data['vlanport_data'], true), 'system_data' => json_encode($device_data['sysstatus_data'], true)])) {
            return json_encode(['success' => 'true']);
        }

        return json_encode(['success' => 'false', 'error' => 'Could not update device']);
    }

    function live($id)
    {
        $device = Device::find($id);

        if ($device) {

            $backups = Backup::where('device_id', $id)->get()->sortByDesc('created_at')->take(15);

            $device->count_vlans = count(json_decode($device->vlan_data)->vlan_element);
            $device->count_trunks = 0;

            $device->format_time = $this->relativeTime(strtotime($device->updated_at));

            $device->ports = json_decode($device->port_data)->port_element;

            $vlan_ports = json_decode($device->vlan_port_data)->vlan_port_element;

            $port_statistic_raw = json_decode($device->port_statistic_data, true)['port_statistics_element'];

            $system = json_decode($device->system_data);
            //ddd($system);
            $device->full_location = Location::find($device->location)->name . " - " . Building::find($device->building)->name . ", " . $device->details . " #" . $device->number;

            // Correct port_statistic array
            foreach ($port_statistic_raw as $key => $value) {
                $port_statistic[$value['id']] = $value;
                unset($port_statistic_raw[$key]);
            }

            // Get trunks
            $trunks = [];
            $tagged = [];
            $untagged = [];
            $ports_online = 0;
            $count_ports = 0;

            foreach ($device->ports as $port) {
                $untagged[$port->id] = "";
                $tagged[$port->id] = [];


                if (str_contains($port->id, 'Trk')) {
                    $device->count_trunks++;
                } else {
                    $count_ports++;
                }

                if ($port->trunk_group != "") {
                    $trunks[$port->trunk_group][] = $port->id;
                    $untagged[$port->id] = "Member of " . $port->trunk_group;
                }

                if($port->is_port_up == "true" and !str_contains($port->id, 'Trk')) {
                    $ports_online++;
                } 

            }

            //ddd($vlan_ports);

            // Get tagged, untagged
            foreach ($vlan_ports as $vlan_port) {
                if ($vlan_port->port_mode == "POM_UNTAGGED" and !str_contains($vlan_port->port_id, "Trk")) {
                    $untagged[$vlan_port->port_id] = "<span class='is-clickable' onclick=\"location.href = '/vlans/".$vlan_port->vlan_id."';\">VLAN " . $vlan_port->vlan_id."</a>";
                } elseif ($vlan_port->port_mode == "POM_TAGGED_STATIC" and !str_contains($vlan_port->port_id, "Trk")) {
                    $tagged[$vlan_port->port_id][] = $vlan_port->vlan_id;
                } elseif ($vlan_port->port_mode == "POM_TAGGED_STATIC" and str_contains($vlan_port->port_id, "Trk")) {
                    $tagged[$vlan_port->port_id][] = $vlan_port->vlan_id;
                }
            }

            $status = $this->isOnline($device->hostname);
            //ddd($device->ports);

            return view('device.live', compact(
                'device',
                'tagged',
                'untagged',
                'trunks',
                'port_statistic',
                'status',
                'system',
                'ports_online',
                'count_ports',
                'backups'
            ));
        }

        return redirect()->back()->withErrors(['error' => 'Could not find device']);
    }


    public function relativeTime($time)
    {
        $d[0] = array(1, "s");
        $d[1] = array(60, "m");
        $d[2] = array(3600, "h");
        $d[3] = array(86400, "d");
        $w = array();
        $return = "";
        $now = time();
        $diff = ($now - $time);
        $secondsLeft = $diff;
        for ($i = 3; $i > -1; $i--) {
            $w[$i] = intval($secondsLeft / $d[$i][0]);
            $secondsLeft -= ($w[$i] * $d[$i][0]);
            if ($w[$i] != 0) $return .= abs($w[$i]) . "" . $d[$i][1] . (($w[$i] > 1) ? '' : '') . " ";
        }
        return ($diff > 0) ? $return = "vor " . $return : $return = "Jetzt gerade";
    }

    public function isOnline($hostname)
    {
        try {
            if ($fp = fsockopen($hostname, 22, $errCode, $errStr, 1)) {
                fclose($fp);
                return "has-text-success	";
            }
            fclose($fp);

            return "has-text-danger";
        } catch (\Exception $e) {
            return "has-text-warning";
        }
    }

    static function updateAllSwitches() {
        $devices = Device::all()->keyBy('id');   
        foreach($devices as $device) {
            // Get login cookie
    
            if (!$auth_cookie = ApiRequestController::login($device->password, $device->hostname)) {

            }
    
            // Get data from device
            if (!$device_data = ApiRequestController::getData($auth_cookie, $device->hostname)) {
                ApiRequestController::logout($auth_cookie, $device->hostname);
            }
    
            ApiRequestController::logout($auth_cookie, $device->hostname);
            if(Device::whereId($device->id)->update(['vlan_data' => json_encode($device_data['vlan_data'], true), 'port_data' => json_encode($device_data['ports_data'], true), 'port_statistic_data' => json_encode($device_data['portstats_data'], true), 'vlan_port_data' => json_encode($device_data['vlanport_data'], true), 'system_data' => json_encode($device_data['sysstatus_data'], true)])) {
            }
        }
    }
}
