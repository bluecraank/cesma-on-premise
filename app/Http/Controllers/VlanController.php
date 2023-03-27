<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVlanRequest;
use App\Models\Device;
use App\Models\DeviceVlanPort;
use App\Models\Location;
use App\Models\Vlan;
use App\Services\VlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\CLog;

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

    public function getPortsByVlan($id)
    {
        $vlan = Vlan::where('vid', $id)->first();
        $vlans = [];
        $devices = Device::all()->sortBy('name')->keyBy('name');
        $ports = [];
        $count_untagged = 0;
        $count_tagged = 0;
        $count_online = 0;
        $has_vlan = 0;

        $untagged = [];
        $tagged = [];

        foreach ($devices as $device) {

            $untagged[$device->id] = [];
            $tagged[$device->id] = [];

            if($device->vlans()->where('vlan_id', $id)->first()) {
                $vlans[$device->id] = $device->vlans()->where('vlan_id', $id)->first()->id;

                $vlanports[$device->id] = $device->vlanports()->where('device_vlan_id', $vlans[$device->id])->get();

                $has_vlan++;
            }   
        }

        foreach($vlanports as $key => $device) {
            foreach($device as $vlanport) {
                $port = $vlanport->devicePort()->first();
                
                if($port->link) {
                    $count_online++;
                }

                if($vlanport->is_tagged) {
                    $tagged[$key][] = $port->name;
                    $count_tagged++;
                } else {
                    $untagged[$key][] = $port->name;
                    $count_untagged++;
                }
            }
        }


        // dd($untagged, $tagged);

        return view('vlan.details', compact(
            'has_vlan',
            'count_untagged',
            'count_tagged',
            'count_online',
            'devices',
            'untagged',
            'tagged',
            'vlan'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vid' => 'required|integer|unique:vlans|between:1,4096',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'ip_range' => 'nullable|string',
            'scan' => 'nullable|string',
            'sync' => 'nullable|string',
            'location_id' => 'required|integer|exists:locations,id'
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

        $request->merge(['is_client_vlan' => true]);

        VlanService::createVlan($request, $scan, $sync);
        CLog::info("VLAN", "VLAN {$request->input('name')} ({$request->input('vid')}) created");
        return redirect()->back()->with('success', 'VLAN created');
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

        VlanService::updateVlan($request, $scan, $sync, $is_client_vlan);
        return redirect()->back()->with('success', __('Msg.VlanUpdated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vlan  $vlan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $vlan)
    {
        $find = Vlan::findOrFail($vlan->id);
        if ($find->delete()) {
            CLog::info("VLAN", "VLAN {$find->name} ({$find->vid}) deleted");
            return redirect()->back()->with('success', __('Msg.VlanDeleted'));
        }

        CLog::error("VLAN", "Could not delete VLAN {$find->name} ({$find->vid})");
        return redirect()->back()->withErrors(['message' => 'Could not delete VLAN']);
    }
}
