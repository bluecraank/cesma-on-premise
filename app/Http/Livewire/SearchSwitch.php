<?php

namespace App\Http\Livewire;

use App\Models\Device;
use Livewire\Component;

class SearchSwitch extends Component
{
    public $searchTerm;
    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('livewire.search-switch',[
            'devices' => Device::where('name','like', $searchTerm)->get(),
            'https' => env('APP_HTTPS')
        ]);
    }
}
