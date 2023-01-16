<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVlanRequest;
use App\Http\Requests\UpdateVlanRequest;
use App\Models\Device;
use App\Models\Vlan;
use Illuminate\Http\Request;

class VlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $vlans = Vlan::all()->sortBy('vid');

        return view('vlan.index', compact(
            'vlans'
        ));
    }

    static function AddVlansFromDevice($vlans, $device, $location) {
        foreach($vlans as $vlan) {
            $vlan = Vlan::firstOrCreate([
                'vid' => $vlan['vlan_id'],
            ], [
                'name' => $vlan['name'],
                'description' => "VLAN " . $vlan['vlan_id'] . " found on " . $device,
                'location_id' => $location,
            ]);
        }
    }


    public function getPortsByVlan($vlan) {
        $vlan_db = Vlan::where('vid', $vlan)->first();
        $vlans = [];
        $devices = Device::all()->sortBy('name');
        $ports = [];
        $count_untagged = 0;
        $count_tagged = 0;
        $count_online = 0;
        $has_vlan = 0;

        foreach($devices as $device) {
            $ports[$device->name] = [];

            $found_on = false;
            $vlans = json_decode($device->vlan_port_data, true);
            $port_data = json_decode($device->port_data, true);
            
            foreach($vlans as $port_vlan_key => $port_vlan) {
                if($port_vlan['vlan_id'] == $vlan) {
                    if(!$found_on) {
                        $found_on = true;
                        $has_vlan++;
                    }

                    if(isset($port_vlan['is_tagged']) and !$port_vlan['is_tagged']) {
                        $count_untagged++;
                        $ports[$device->name]['untagged'][] = $port_vlan['port_id'];

                        if(isset($port_data[$port_vlan['port_id']]) and $port_data[$port_vlan['port_id']]['is_port_up']) {
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateVlanRequest  $request
     * @param  \App\Models\Vlan  $vlan
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVlanRequest $request, Vlan $vlan)
    {
        if(Vlan::whereId($request['id'])->update([
            'name' => $request['name'],
            'description' => $request['description'],
            'ip_range' => $request['ip_range'] ?? null,
            'scan' => ($request['scan'] == "on") ? true : false,
            'sync' => ($request['sync'] == "on") ? true : false
        ])) {
            $vlanD = Vlan::whereId($request['id'])->first();
            LogController::log('VLAN aktualisiert', '{"name": "' . $request->name . '", "vid": "' . $vlanD->vid . '", "description": "' . $request->description . '"}');

            return redirect()->back()->with('success', 'VLAN updated successfully');
        }
            return redirect()->back()->withErrors(['error' => 'VLAN could not be updated']);
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
        if($find->delete()) {
            LogController::log('VLAN gelöscht', '{"name": "' . $find->name . '", "vid": "' . $find->vid . '", "description": "' . $find->description . '"}');

            return redirect()->back()->with('success', 'VLAN deleted');
        }
        return redirect()->back()->withErrors(['error' => 'Could not delete VLAN']);

    }
}
