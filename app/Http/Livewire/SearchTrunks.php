<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Device;

class SearchTrunks extends Component
{
    public $searchTerm;
    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('livewire.search-trunks',[
            'devices' => Device::where('name','like', $searchTerm)->get()
        ]);
    }
}
