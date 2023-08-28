<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Vlan;
use App\Services\VlanService;
use App\Helper\CLog;
use App\Http\Requests\StoreVlanRequest;
use Illuminate\Support\Facades\Auth;

class VlanController extends Controller
{
    public function showVlanDetails($id)
    {
        $vlan = Vlan::where('id', $id)->where('site_id', Auth::user()->currentSite()->id)->firstOrFail();
        $vlans = [];
        $devices = Device::where('site_id', Auth::user()->currentSite()->id)->get()->sortBy('name')->keyBy('name');
        $ports = [];
        $count_untagged = 0;
        $count_tagged = 0;
        $count_online = 0;
        $has_vlan = 0;

        $vlanports = [];
        $untagged = [];
        $tagged = [];

        foreach ($devices as $device) {

            $untagged[$device->id] = [];
            $tagged[$device->id] = [];

            if($device->vlans()->where('vlan_id', $vlan->vid)->first()) {
                $vlans[$device->id] = $device->vlans()->where('vlan_id', $vlan->vid)->first()->id;

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

    public function store(StoreVlanRequest $request)
    {

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
}
