<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuildingRequest;
use App\Http\Requests\UpdateBuildingRequest;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuildingController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBuildingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBuildingRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:buildings|max:100',
            'location_id' => 'required|integer',
        ])->validate();

        if ($validator and Building::create($request->all())) {
            LogController::log('Gebäude erstellt', '{"name": "' . $request->name . '", "location_id": "' . $request->location_id . '"}');

            return redirect()->back()->with('success', __('Msg.LocationCreated'));
        }
        return redirect()->back()->withErrors(['message' => 'Building could not be created']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBuildingRequest  $request
     * @param  \App\Models\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBuildingRequest $request, Building $building)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:buildings|max:100',
        ])->validate();

        if ($validator and $building->whereId($request->input('id'))->update($request->except(['_token', '_method']))) {
            LogController::log('Gebäude aktualisiert', '{"name": "' . $request->name . '"}');

            return redirect()->back()->with('success', __('Msg.LocationUpdated'));
        }
        return redirect()->back()->withErrors(['message' => 'Building could not be updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $building)
    {
        $find = Building::find($building->input('id'));
        if ($find->delete()) {
            LogController::log('Gebäude gelöscht', '{"name": "' . $building->name . '"}');

            return redirect()->back()->with('success', __('Msg.LocationDeleted'));
        }
        return redirect()->back()->with('message', 'Could not delete building');
    }
}
