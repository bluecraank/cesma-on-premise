<?php

namespace App\Http\Livewire;

use App\Http\Controllers\DeviceController;
use App\Models\Device;
use App\Models\Location;
use App\Models\Building;
use App\Services\DeviceService;
use App\Traits\WithLogin;


use Livewire\Component;

class SearchDevices extends Component
{
    use WithLogin;

    public $searchTerm = "";

    public function mount()
    {
        $this->checkLogin();
    }

    public function render()
    {
        $searchTerm = '%' . $this->searchTerm . '%';
        $https = config('app.https', 'http://');

        $devices = Device::where('name', 'like', $searchTerm)->orWhere('hostname', 'like', $searchTerm)->get()->sortBy('id');
        foreach ($devices as $device) {
            $device->online = DeviceService::isOnline($device->hostname);
        }

        return view('switch.switch-overview-livew', [
            'devices' => $devices,
            'https' => $https
        ]);
    }
}
