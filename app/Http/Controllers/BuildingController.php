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
            CLog::info("Building", "Create building {$request->name}");
            return redirect()->back()->with('success', __('Msg.BuildingCreated'));
        }

        CLog::error("Building", "Could not create building {$request->name}");
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
        if($building->whereId($request->input('id'))->update($request->except(['_token', '_method']))) {

            CLog::info("Building", "Update building {$request->name}");
            return redirect()->back()->with('success', __('Msg.BuildingUpdated'));
        }

        CLog::error("Building", "Could not update building {$request->name}");
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
        $validator = Validator::make($building->all(), [
            'id' => 'required|integer|exists:buildings,id',
        ])->validate();

        $find = Building::find($building->input('id'));

        if($find->rooms()->count() > 0) {
            return redirect()->back()->withErrors(['message' => 'Building could not be deleted, because it has rooms assigned to it.']);
        }

        if ($find->delete()) {
            CLog::info("Building", "Delete building {$find->name}");

            return redirect()->back()->with('success', __('Msg.BuildingDeleted'));
        }

        CLog::error("Building", "Could not delete building {$find->name}");
        return redirect()->back()->with('message', 'Could not delete building');
    }
}
