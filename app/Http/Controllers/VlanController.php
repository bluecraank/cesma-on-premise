<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vlan;

class VlanController extends Controller
{
    // Auch unter Livewire/SearchVlans.php
    function overview() {
        $vlans = Vlan::all();

        return view('vlan.overview', compact(
            'vlans'
        ));
    }
}
