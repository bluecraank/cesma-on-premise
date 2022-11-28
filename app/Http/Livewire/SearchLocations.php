<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Location;

class SearchLocations extends Component
{
    public $searchTerm;
    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('livewire.search-locations',[
            'locations' => Location::where('name','like', $searchTerm)->get(),
        ]);
    }
}
