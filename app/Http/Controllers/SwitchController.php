<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class SwitchController extends Controller
{
    
    // Auch unter Livewire/SearchSwitch.php
    function overview() {
        $devices = Device::all();
        $https = 'http://';

        return view('device.overview', compact(
            'devices',
            'https'
        ));
    }

    function trunks() {
        $devices = Device::all();

        return view('device.trunks', compact(
            'devices',
        ));
    }
}
