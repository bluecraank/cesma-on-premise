<?php

namespace App\Http\Livewire;

use App\Models\Device;
use App\Models\Client;
use App\Models\MacVendors;
use App\Models\Vlan;
use App\Traits\WithLogin;


use Livewire\Component;

class SearchClients extends Component
{
    use WithLogin;

    public $cHOSTNAME, $cIP, $cMAC, $cVLAN, $cSWITCH, $cPORT, $cSTATUS, $cTYPE;

    public function mount() {
        $this->checkLogin();
    } 

    public function render()
    {
        $devices = Device::all()->keyBy('id');
        $count_clients = Client::count();
        $vlans = Vlan::all()->sortBy('vid')->keyBy('vid');
        $vendors = MacVendors::all()->keyBy('mac_prefix');
        

        $hostname = $this->cHOSTNAME;
        $ip = $this->cIP;
        $mac = $this->cMAC;
        $vlan = $this->cVLAN;
        $switch = $this->cSWITCH;
        $port = $this->cPORT;
        $status = $this->cSTATUS;
        $type = $this->cTYPE;


        $clients = Client::where(function ($query) use($hostname, $ip, $mac, $vlan, $switch, $port, $status, $type) {
            if ($hostname) {
                $query->where('hostname', 'like', '%' . $hostname . '%');
            }
            if ($ip) {
                $query->where('ip_address', 'like', '%' . $ip . '%');
            }
            if ($mac) {
                $filtered = str_replace(['-', ':'], '', $mac);
                $query->where('mac_address', 'like', '%' . $filtered . '%');
            }
            if ($vlan and $vlan != 'all') {
                $query->where('vlan_id', '=', $vlan);
            }
            if ($switch and $switch != 'all') {
                $query->where('switch_id', '=', $switch);
            }
            if ($port) {
                $query->where('port_id', 'like', '%' . $port . '%');
            }
            if ($status and $status != 'all') {
                $query->where('online', '=', $status);
            }
            if ($type and $type != 'all') {
                $query->where('type', '=', $type);
            }
        })->paginate(500);

        $count_result = count($clients);

        return view('client.index_', [
            'devices' => $devices,
            'vlans' => $vlans,
            'count_clients' => $count_clients,
            'count_results' => $count_result,
            'clients' => $clients,
            'vendors' => $vendors,
        ]);
    }
}
