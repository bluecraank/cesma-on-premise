<?php

namespace App\Http\Livewire;

use App\Models\Device;
use App\Models\Location;
use App\Models\Building;
use App\Traits\WithLogin;


use Livewire\Component;

class SearchSwitch extends Component
{
    use WithLogin;

    public $searchTerm = "";

    public function mount() {
        $this->checkLogin();
    } 

    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        $https = config('app.https', 'http://');
        return view('livewire.search-switch',[
            'devices' => Device::where('name', 'like', $searchTerm)->orWhere('hostname', 'like', $searchTerm)->get()->sortBy('name'),
            'locations' => Location::all()->keyBy('id'),
            'buildings' => Building::all()->keyBy('id'),
            'https' => $https
        ]);
    }
}
