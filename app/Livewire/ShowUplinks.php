<?php

namespace App\Livewire;

use App\Models\DeviceUplink;
use Livewire\Component;

class ShowUplinks extends Component
{
    public function render()
    {
        $uplinks = DeviceUplink::all()->keyBy('id')->groupBy('device_id')->toArray();
        dd($uplinks);
        return view('livewire.show-uplinks');
    }
}
