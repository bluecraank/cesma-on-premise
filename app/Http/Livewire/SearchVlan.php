<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Vlan;
use App\Traits\WithLogin;


class SearchVlan extends Component
{
    use WithLogin;

    public $searchTerm = "";

    public function mount() {
        $this->checkLogin();
    } 

    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('livewire.search-vlan',[
            'vlans' => Vlan::where('name','like', $searchTerm)->get()->sortBy('vid'),
        ]);
    }
}
