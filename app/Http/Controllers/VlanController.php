<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVlanRequest;
use App\Models\Device;
use App\Models\Location;
use App\Models\Vlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vlans = Vlan::all()->sortBy('vid');
        $locations = Location::all()->sortBy('id');

        return view('vlan.vlan-overview', compact(
            'vlans',
            'locations'
        ));
    }

    static function AddVlansFromDevice($vlans, $device, $location)
    {
        foreach ($vlans as $vlan) {
            $vlan = Vlan::firstOrCreate([
                'vid' => $vlan['vlan_id'],
            ], [
                'name' => $vlan['name'],
                'description' => "VLAN " . $vlan['vlan_id'] . " found on " . $device,
                'location_id' => $location,
            ]);
        }
    }


    public function getPortsByVlan($vlan)
    {
        $vlan_db = Vlan::where('vid', $vlan)->first();
        $vlans = [];
        $devices = Device::all()->sortBy('name');
        $ports = [];
        $count_untagged = 0;
        $count_tagged = 0;
        $count_online = 0;
        $has_vlan = 0;

        foreach ($devices as $device) {
            $ports[$device->name] = [];

            $found_on = false;
            $vlans = json_decode($device->vlan_port_data, true);
            $port_data = json_decode($device->port_data, true);

            foreach ($vlans as $port_vlan_key => $port_vlan) {
                if ($port_vlan['vlan_id'] == $vlan) {
                    if (!$found_on) {
                        $found_on = true;
                        $has_vlan++;
                    }

                    if (isset($port_vlan['is_tagged']) and !$port_vlan['is_tagged']) {
                        $count_untagged++;
                        $ports[$device->name]['untagged'][] = $port_vlan['port_id'];

                        if (isset($port_data[$port_vlan['port_id']]) and $port_data[$port_vlan['port_id']]['is_port_up']) {
                            $count_online++;
                        }
                    } else {
                        $count_tagged++;
                    }
                }
            }
        }

        return view('vlan.details', compact(
            'ports',
            'has_vlan',
            'count_untagged',
            'count_tagged',
            'count_online',
            'vlan_db'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vid' => 'required|integer|unique:vlans',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'ip_range' => 'nullable|string',
            'scan' => 'nullable|string',
            'sync' => 'nullable|string',
            'location' => 'required|integer|exists:locations,id'
        ])->validate();

        if ($request->input('scan') == "on") {
            $scan = true;
        } else {
            $scan = false;
        }

        if ($request->input('sync') == "on") {
            $sync = true;
        } else {
            $sync = false;
        }

        if (!empty($request->input('ip_range')) and (!str_contains($request->input('ip_range'), '/') or substr_count($request->input('ip_range'), '.') != 3)) {
            return redirect()->back()->withErrors(['message' => 'IP range is not valid']);
        }

        if ($vlan = Vlan::create([
            'vid' => $request['vid'],
            'name' => $request['name'],
            'description' => $request['description'],
            'ip_range' => $request['ip_range'] ?? null,
            'scan' => $scan,
            'sync' => $sync,
            'location_id' => $request['location']
        ])) {
            LogController::log('VLAN erstellt', '{"name": "' .  $request['name'] . '", "vid": "' . $request['vid'] . '", "description": "' . $request['description'] . '" "scan": "' . $scan . '", "sync": "' . $sync . '"}');
            return redirect()->route('vlan.index')->with('success', 'VLAN ' . $vlan->name . ' created');
        }

        return redirect()->back()->withErrors(['message' => 'VLAN could not be created']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateVlanRequest  $request
     * @param  \App\Models\Vlan  $vlan
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVlanRequest $request, Vlan $vlan)
    {

        if (!empty($request->input('ip_range')) and (!str_contains($request->input('ip_range'), '/') or substr_count($request->input('ip_range'), '.') != 3)) {
            return redirect()->back()->withErrors(['message' => 'IP range is not valid']);
        }

        if ($request->input('scan') == "on") {
            $scan = true;
        } else {
            $scan = false;
        }

        if ($request->input('sync') == "on") {
            $sync = true;
        } else {
            $sync = false;
        }

        if ($request->input('is_client_vlan') == "on") {
            $is_client_vlan = false;
        } else {
            $is_client_vlan = true;
        }

        if (Vlan::whereId($request['id'])->update([
            'name' => $request['name'],
            'description' => $request['description'],
            'ip_range' => $request['ip_range'] ?? null,
            'scan' => $scan,
            'sync' => $sync,
            'is_client_vlan' => $is_client_vlan,
        ])) {
            $vlanD = Vlan::whereId($request['id'])->first();
            LogController::log('VLAN aktualisiert', '{"name": "' . $request->name . '", "vid": "' . $vlanD->vid . '", "description": "' . $request->description . '" "scan": "' . $scan . '", "sync": "' . $sync . '"}');

            return redirect()->back()->with('success', 'VLAN updated successfully');
        }
        return redirect()->back()->withErrors(['message' => 'VLAN could not be updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vlan  $vlan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $vlan)
    {
        $find = Vlan::find($vlan->id);
        if ($find->delete()) {
            LogController::log('VLAN gelöscht', '{"name": "' . $find->name . '", "vid": "' . $find->vid . '"}');

            return redirect()->back()->with('success', 'VLAN deleted');
        }
        return redirect()->back()->withErrors(['message' => 'Could not delete VLAN']);
    }
}
