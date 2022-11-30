<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Location;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class SearchLocations extends Component
{
    use AuthorizesRequests;

    public function mount()
    {
        $this->authorize();
    }

    public $searchTerm;
    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('livewire.search-locations',[
            'locations' => Location::where('name','like', $searchTerm)->get(),
        ]);
    }
}
