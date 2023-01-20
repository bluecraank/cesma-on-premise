<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Models\Location;
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

        if ($validator and Location::create($request->all())) {
            LogController::log('Standort erstellt', '{"name": "' . $request->name . '"}');

            return redirect()->back()->with('success', 'Location created successfully');
        }
        return redirect()->back()->withErrors(['error' => 'Location could not be created']);
    }
}
