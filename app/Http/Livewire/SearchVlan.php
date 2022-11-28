<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Vlan;

class SearchVlan extends Component
{
    public $searchTerm;
    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('livewire.search-vlan',[
            'vlans' => Vlan::where('name','like', $searchTerm)->get()
        ]);
    }
}
