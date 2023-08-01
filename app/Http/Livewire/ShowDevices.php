<?php

namespace App\Http\Livewire;

use App\Models\Device;
use App\Models\Building;
use App\Models\Notification;
use App\Models\Room;
use App\Models\Site;
use App\Services\DeviceService;
use App\Services\PublicKeyService;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowDevices extends Component
{
    use WithLogin;

    public $searchTerm = "";
    public $numberOfEntries = 25;

    public function mount()
    {
        $this->checkLogin();
    }

    public function render()
    {
        $searchTerm = '%' . $this->searchTerm . '%';
        $https = config('app.https', 'http://');

        $devices = Device::where('site_id', Auth::user()->currentSite()->id)->where(function ($query) use($searchTerm) {
            $query->where('name', 'like', $searchTerm)->orWhere('hostname', 'like', $searchTerm);
        })->paginate($this->numberOfEntries, ['*'], 'devices');
        
        foreach ($devices as $device) {
            $device->online = DeviceService::isOnline($device->hostname);
        }

        // Sort devices by name in natural order
        $devices->sort(function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        $buildings = Building::where('site_id', Auth::user()->currentSite()->id)->get();

        return view('livewire.show-devices', [
            'devices' => $devices,
            'https' => $https,
            'sites' => Site::all(),
            'buildings' => $buildings,
            'rooms' => Room::whereIn('building_id', $buildings->pluck('id')->toArray())->get(),
            'keys_list' => PublicKeyService::getPubkeysDescriptionAsArray(),
            'notifications' => Notification::latest()->take(10)->get(),
        ]);
    }
}
