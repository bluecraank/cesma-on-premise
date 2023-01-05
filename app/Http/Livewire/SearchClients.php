<?php

namespace App\Http\Livewire;

use App\Models\Device;
use App\Models\Endpoint;
use App\Traits\WithLogin;


use Livewire\Component;

class SearchClients extends Component
{
    use WithLogin;

    public $searchTerm = "";

    public function mount() {
        $this->checkLogin();
    } 

    public function render()
    {
        $devices = Device::all()->keyBy('id');

        $searchTerm = '%'.$this->searchTerm.'%';

        $searchTerm = str_Replace([" ", ";", ":", "-"], "", $searchTerm);

        return view('livewire.search-clients',[
            'clients' => Endpoint::where('hostname', 'like', $searchTerm)
            ->orWhere('ip_address', 'like', $searchTerm)
            ->orWhere('mac_address', 'like', $searchTerm)
            ->orWhere('vlan_id', 'like', $searchTerm)
            ->orWhere('port_id', 'like', $searchTerm)
            ->orWhere('switch_id', 'like', $searchTerm)
            ->get()->sortBy('name'),
            'devices' => $devices
        ]);
    }
}
