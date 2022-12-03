<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVlanRequest;
use App\Http\Requests\UpdateVlanRequest;
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

        return view('vlan.overview', compact(
            'vlans'
        ));
    }

    static function AddVlansFromDevice($vlans, $device, $location) {
        foreach($vlans['vlan_element'] as $vlan) {
            $vlan = Vlan::firstOrCreate([
                'name' => $vlan['name'],
                'vid' => $vlan['vlan_id'],
                'description' => "VLAN " . $vlan['vlan_id'] . " found on " . $device,
                'location_id' => $location,
            ]);
        }
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
     * @param  \App\Http\Requests\StoreVlanRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVlanRequest $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vlan  $vlan
     * @return \Illuminate\Http\Response
     */
    public function show(Vlan $vlan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Vlan  $vlan
     * @return \Illuminate\Http\Response
     */
    public function edit(Vlan $vlan)
    {
        //
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
        if(Vlan::whereId($request['id'])->update($request->validated())) {
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
