<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{

    // Auch unter Livewire/SearchLocations.php
    public function overview()
    {
        $locations = Location::all();
        return view('locations.overview', compact('locations'));
    }
}
