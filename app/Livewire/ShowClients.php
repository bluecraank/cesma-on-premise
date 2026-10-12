<?php

namespace App\Livewire;


use App\Helper\Utilities;
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

    // Back to the first page when a filter changes, otherwise the results may be on an earlier page
    public function updating($property)
    {
        if (in_array($property, ['hostname', 'ip', 'mac', 'vlan', 'switch', 'port', 'type', 'numberOfEntries'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $devices = Device::where('site_id', Auth::user()->currentSite()->id)->get()->keyBy('id')->sortBy('name');
        $count_clients = Client::where('site_id', Auth::user()->currentSite()->id)->count();
        $vlans = Vlan::where('site_id', Auth::user()->currentSite()->id)->get()->sortBy('vid')->keyBy('vid');
        $vendors = MacVendor::all()->keyBy('mac_prefix');
        $types = MacType::all()->sortBy('type')->unique('type');

        $hostname = $this->hostname;
        $ip = $this->ip;
        $mac = $this->mac;
        $vlan = $this->vlan;
        $switch = $this->switch;
        $port = $this->port;
        $type = $this->type;


        $clients = Client::where('site_id', Auth::user()->currentSite()->id)->where(function ($query) use ($hostname, $ip, $mac, $vlan, $switch, $port, $type) {
            if (trim((string) $hostname) !== '') {
                $query->where('hostname', 'like', Utilities::like($hostname));
            }
            if (trim((string) $ip) !== '') {
                $query->where('ip_address', 'like', Utilities::like($ip));
            }
            if (trim((string) $mac) !== '') {
                // MAC addresses are stored lowercase without separators, accept every common notation
                $filtered = strtolower(str_replace(['-', ':', '.', ' '], '', $mac));
                $query->where('mac_address', 'like', Utilities::like($filtered));
            }
            if ($vlan and $vlan != 'all') {
                $query->where('vlan_id', '=', $vlan);
            }
            if ($switch and $switch != 'all') {
                $query->where('device_id', '=', $switch);
            }
            if (trim((string) $port) !== '') {
                // Exact port, "1" must not also find 10, 11, 21... ("5" also finds "1/1/5")
                $port = trim($port);
                $query->where(function ($query) use ($port) {
                    $query->where('port_id', $port)->orWhere('port_id', 'like', '%/' . addcslashes($port, '%_\\'));
                });
            }
            if ($type and $type != 'all') {
                $query->where('type', '=', $type);
            }
        })->orderBy('updated_at', 'desc');

        // Most recently seen clients first
        $clients = $clients->paginate($this->numberOfEntries ?? 25);

        // Total number of matches, not only the current page
        $count_result = $clients->total();

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
