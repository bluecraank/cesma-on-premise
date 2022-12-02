<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Models\Device;
use App\Models\Location;
use App\Models\Building;
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
    function overview() {
        $devices = Device::all();
        $locations = Location::all();
        $buildings = Building::all();
        $https = 'http://';

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
        if(filter_var($request->input('hostname'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $hostname = gethostbyaddr($request->input('hostname')) or $request->input('hostname');
        }

        // Encrypt password before store
        $encrypted_pw = EncryptionController::encrypt($request->all()['password']);
        $request->merge(['password' => $encrypted_pw]);
        $request->merge(['hostname' => $hostname]);

        // Get login cookie
        if(!$auth_cookie = ApiRequestController::login($encrypted_pw, $hostname)) {
            return redirect()->back()->withErrors(['error' => 'Could not login to device']);
            //return false;
        }

        // Get data from device
        if(!$device_data = ApiRequestController::getData($auth_cookie, $hostname)) {
            ApiRequestController::logout($auth_cookie, $hostname);
            return redirect()->back()->withErrors(['error' => 'Could not get data from device']);
            //return false;
        }
    
        // Merge data from device with requests
        ApiRequestController::logout($auth_cookie, $hostname);
        $request->merge(['data' => json_encode($device_data, true)]);
        
        if($validator) {
        //if($validator AND Device::create($request->all())) {
            ddd($device_data);
            VlanController::AddVlansFromDevice($device_data['vlan_data'], $request->input('name'), $request->input('location'));

            //return redirect()->back()->with('success', 'Device added');
        }

        //return redirect('/')->withErrors($validator);
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
        //
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
        if($find->delete()) {
            return redirect()->back()->with('success', 'Device deleted');
        }
        return redirect()->back()->with('error', 'Could not delete device');
    }

    function trunks() {
        $devices = Device::all();

        return view('device.trunks', compact(
            'devices',
        ));
    }
}
