<?php

namespace App\Http\Livewire;

use App\Models\Device;
use App\Models\Location;
use App\Models\Building;

use Livewire\Component;

class SearchSwitch extends Component
{
    public $searchTerm;
    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('livewire.search-switch',[
            'devices' => Device::where('name', 'like', $searchTerm)->orWhere('hostname', 'like', $searchTerm)->get(),
            'locations' => Location::all(),
            'buildings' => Building::all(),
            'https' => env('APP_HTTPS')
        ]);
    }
}
