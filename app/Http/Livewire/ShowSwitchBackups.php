<?php

namespace App\Http\Livewire;

use App\Models\Device;
use App\Models\DeviceBackup;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\WithLogin;
use Livewire\WithPagination;

class ShowSwitchBackups extends Component
{
    use WithLogin;
    use WithPagination;

    public $numberOfEntries = 25;
    public $device_id;

    public function mount(Device $device)
    {
        $this->device_id = $device->id;
        $this->checkLogin();
    }

    public function render()
    {
        $device = Device::where('id', $this->device_id)->first();
        $backups = $device->backups()->latest()->paginate($this->numberOfEntries);

        return view('livewire.show-switch-backups', [
            'backups' => $backups,
            'device' => $device,
        ]);
    }
}
