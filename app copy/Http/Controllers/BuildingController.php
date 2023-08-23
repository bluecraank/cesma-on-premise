<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuildingRequest;
use App\Http\Requests\UpdateBuildingRequest;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\CLog;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;

class BuildingController extends Controller
{
    public function store(StoreBuildingRequest $request)
    {
        $building = Building::create($request->except('_token', '_method'));
        if ($building) {
            CLog::info("Building", "Building created", null, $request->name);
            return redirect()->back()->with('success', __('Building successfully created'));
        }

        return redirect()->back()->withErrors(['message' => 'Building could not be created']);
    }
}
