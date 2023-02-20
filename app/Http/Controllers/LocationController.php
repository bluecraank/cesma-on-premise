<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Models\Building;
use App\Models\Location;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\CLog;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('location.location-overview', [
            'locations' => Location::all(),
            'buildings' => Building::all(),
            'rooms' => Room::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreLocationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLocationRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:locations|max:100',
        ])->validate();

        if (Location::create($request->all())) {
            CLog::info("Location", "Create location {$request->name}");

            return redirect()->back()->with('success', __('Msg.LocationCreated'));
        }

        CLog::error("Location", "Could not create location {$request->name}");
        return redirect()->back()->withErrors(['message' => 'Location could not be created']);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'id' => 'required|integer|exists:locations,id',
        ])->validate();

        if (Location::find($request->id)->update($request->all())) {
            CLog::info("Location", "Update location {$request->name}");

            return redirect()->back()->with('success', __('Msg.LocationUpdated'));
        }

        CLog::error("Location", "Could not update location {$request->name}");
        return redirect()->back()->withErrors(['message' => 'Location could not be updated']);
    }

    public function destroy(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:locations,id',
        ])->validate();

        $find = Location::find($request->id);

        if($find->buildings()->count() > 0) {
            return redirect()->back()->withErrors(['message' => 'Location could not be deleted because it contains buildings']);
        }

        if ($find->delete()) {
            CLog::info("Location", "Delete location {$find->name}");

            return redirect()->back()->with('success', __('Msg.LocationDeleted'));
        }

        CLog::error("Location", "Could not delete location {$find->name}");
        return redirect()->back()->withErrors(['message' => 'Location could not be deleted']);
    }
}
