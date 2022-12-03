<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuildingRequest;
use App\Http\Requests\UpdateBuildingRequest;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
// ANSONSTEN GUZZLE!!!

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

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

         if($validator AND Building::create($request->all())) {
            LogController::log('Gebäude erstellt', '{"name": "' . $request->name . '", "location_id": "' . $request->location_id . '"}');

            return redirect()->back()->with('success', 'Building created successfully');
         } 
            return redirect()->back()->withErrors(['error' => 'Building could not be created']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function show(Building $building)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function edit(Building $building)
    {
        //
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

         if($validator AND $building->whereId($request->input('id'))->update($request->except(['_token', '_method']))) {
            LogController::log('Gebäude aktualisiert', '{"name": "' . $request->name . '"}');

            return redirect()->back()->with('success', 'Building updated successfully');
         } 
            return redirect()->back()->withErrors(['error' => 'Building could not be updated']);
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
        if($find->delete()) {
            LogController::log('Gebäude gelöscht', '{"name": "' . $building->name . '"}');

            return redirect()->back()->with('success', 'Building deleted');
        }
        return redirect()->back()->with('error', 'Could not delete building');
    }
}
