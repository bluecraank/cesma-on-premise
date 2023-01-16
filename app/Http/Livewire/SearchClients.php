<?php

namespace App\Http\Livewire;

use App\Models\Device;
use App\Models\Client;
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

        // MAC
        if(substr_count($searchTerm, ":") == 5) {
            $searchTerm = str_Replace(":", "", $searchTerm);
        }

        // PC
        if(substr_count($searchTerm, "-") > 1) {
            $searchTerm = str_Replace([";", ":"], "", $searchTerm);
        }
    
        return view('client.index_',[
            'clients' => Client::where(function ($query) use ($searchTerm) {
                $hide_vlans = explode(",", config('app.hide_vlans'));
                if(str_contains($searchTerm, "online=1")) {
                    $query->where('online', '=', 1);
                }
                foreach($hide_vlans as $vlan) {
                    $query->where('vlan_id', 'not like', $vlan);
                }
            })->where(function ($query) use ($searchTerm) {
                $query->where('hostname', 'like', $searchTerm)
                    ->orWhere('ip_address', 'like', $searchTerm)
                    ->orWhere('mac_address', 'like', $searchTerm)
                    ->orWhere('vlan_id', 'like', $searchTerm)
                    ->orWhere('port_id', 'like', $searchTerm)
                    ->orWhere('switch_id', 'like', $searchTerm);
            })->paginate(1000),
            'devices' => $devices
        ]);
    }
}
