<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Location;
use App\Models\Building;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\WithLogin;


class SearchLocations extends Component
{
    use WithLogin;

    public $searchTerm = "";

    public function mount() {
        $this->checkLogin();
    } 

    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('location.location-overview-livew',[
            'buildings' => Building::where('name','like', $searchTerm)->get()->sortBy('name'),
            'locations' => Location::all()->keyBy('id'),
        ]);
    }
}
