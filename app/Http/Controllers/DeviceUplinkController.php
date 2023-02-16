<?php

namespace App\Http\Controllers;

use App\Models\Device;

class DeviceUplinkController extends Controller
{
    public function index() {
        $devices = Device::all();
        return view('switch.view_uplinks', compact('devices'));
    }
}
