<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Models\Building;
use App\Models\Location;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            // LogController::log('Standort erstellt', '{"name": "' . $request->name . '"}');

            return redirect()->back()->with('success', __('Msg.LocationCreated'));
        }
        return redirect()->back()->withErrors(['message' => 'Location could not be created']);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'id' => 'required|integer|exists:locations,id',
        ])->validate();

        if (Location::find($request->id)->update($request->all())) {
            // LogController::log('Standort bearbeitet', '{"name": "' . $request->name . '"}');

            return redirect()->back()->with('success', __('Msg.LocationUpdated'));
        }
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
            // LogController::log('Standort gelöscht', '{"id": "' . $request->id . '"}');

            return redirect()->back()->with('success', __('Msg.LocationDeleted'));
        }
        return redirect()->back()->withErrors(['message' => 'Location could not be deleted']);
    }
}
