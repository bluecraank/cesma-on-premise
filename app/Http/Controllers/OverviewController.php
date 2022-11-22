<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    public function index() {

        // Alle Switche laden sortiert nach Name
        $devices = Device::all()->sortBy('name');
        $https = "https";

        return view('switch', compact('devices', 'https'));
    }
}
