<?php

namespace App\Http\Livewire;

use App\Models\Device;
use App\Models\DeviceBackup;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\WithLogin;
use Livewire\WithPagination;

class ShowBackups extends Component
{
    use WithLogin;
    use WithPagination;

    public $numberOfEntries = 25;

    public function mount()
    {
        $this->checkLogin();
    }

    public function render()
    {
        $backups = DeviceBackup::select('id', 'status', 'created_at', 'device_id')->get()->keyBy('id');
        $devices = Device::where('site_id', Auth::user()->currentSite()->id)->paginate($this->numberOfEntries);

        foreach ($devices as $device) {
            $device->last_backup = $backups->where('device_id', $device->id)->last();
        }

        $devices->sort(function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        return view('livewire.show-backups', [
            'devices' => $devices,
        ]);
    }
}
