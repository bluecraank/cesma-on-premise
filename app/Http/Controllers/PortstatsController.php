<?php

namespace App\Http\Controllers;

use App\Models\Device;

class PortstatsController extends Controller
{
    public function index($id)
    {
        $device = Device::find($id);

        // $portstats = PortStat::where('device_id', $id)->get();

        return view('switch.view_portstats', compact('device', 'ports'));
    }
}
