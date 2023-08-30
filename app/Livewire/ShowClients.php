<?php

namespace App\Livewire;


use App\Models\Device;
use App\Models\Client;
use App\Models\MacType;
use App\Models\MacVendor;
use App\Models\Vlan;
use App\Traits\NumberOfEntries;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class ShowClients extends Component
{
    use WithLogin;
    use WithPagination;
    use NumberOfEntries;

    #[Url]
    public $hostname, $ip, $mac, $vlan, $switch, $port, $type;
    public $numberOfEntries = 25;

    public function mount()
    {
        $this->checkLogin();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $devices = Device::where('site_id', Auth::user()->currentSite()->id)->get()->keyBy('id');
        $count_clients = Client::count();
        $vlans = Vlan::where('site_id', Auth::user()->currentSite()->id)->get()->sortBy('vid')->keyBy('vid');
        $vendors = MacVendor::all()->keyBy('mac_prefix');
        $types = MacType::all()->sortBy('type')->unique();

        $hostname = $this->hostname;
        $ip = $this->ip;
        $mac = $this->mac;
        $vlan = $this->vlan;
        $switch = $this->switch;
        $port = $this->port;
        $type = $this->type;


        $clients = Client::where('site_id', Auth::user()->currentSite()->id)->where(function ($query) use ($hostname, $ip, $mac, $vlan, $switch, $port, $type) {
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
                $query->where('device_id', '=', $switch);
            }
            if ($port) {
                $query->where('port_id', 'like', '%' . $port . '%');
            }
            if ($type and $type != 'all') {
                $query->where('type', '=', $type);
            }
        })->orderBy('updated_at', 'desc');

        $clients = $clients->paginate($this->numberOfEntries ?? 25);

        // Sort clients by name in natural order
        $clients->sort(function ($a, $b) {
            return strnatcmp($a['hostname'], $b['hostname']);
        });

        $count_result = count($clients);

        return view('livewire.show-clients', [
            'devices' => $devices,
            'vlans' => $vlans,
            'count_clients' => $count_clients,
            'count_results' => $count_result,
            'clients' => $clients,
            'vendors' => $vendors,
            'types' => $types,
        ]);
    }
}
