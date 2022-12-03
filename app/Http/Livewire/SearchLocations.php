<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Location;
use App\Models\Building;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class SearchLocations extends Component
{
    use AuthorizesRequests;

    public $searchTerm;
    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('livewire.search-locations',[
            'buildings' => Building::where('name','like', $searchTerm)->get(),
            'locations' => Location::all()->keyBy('id'),
        ]);
    }
}
