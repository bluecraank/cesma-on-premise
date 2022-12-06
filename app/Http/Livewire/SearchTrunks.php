<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Device;
use App\Traits\WithLogin;


class SearchTrunks extends Component
{
    use WithLogin;

    public $searchTerm = "";

    public function mount() {
        $this->checkLogin();
    } 

    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('livewire.search-trunks',[
            'devices' => Device::where('name','like', $searchTerm)->get()->sortBy('name'),
        ]);
    }
}
