<?php

namespace App\Livewire;


use App\Models\Device;
use App\Models\DeviceBackup;
use App\Traits\NumberOfEntries;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\WithLogin;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class ShowBackups extends Component
{
    use \App\Traits\ChecksRole;
    use WithLogin;
    use WithPagination;
    use NumberOfEntries;

    public $numberOfEntries = 25;

    #[Url]
    public $search = '';

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
        $search = \App\Helper\Utilities::like($this->search);

        $devices = Device::where('site_id', Auth::user()->currentSite()->id)->where(function ($query) use ($search) {
            $query->where('name', 'like', $search)->orWhere('hostname', 'like', $search);
        })->orderBy('name')->paginate($this->numberOfEntries ?? 25);

        // Big performance improve if we only get the last backup for each device
        foreach ($devices as $device) {
            $device->last_backup = DeviceBackup::where('device_id', $device->id)->latest()->first();
        }

        $newDevices = $devices->sort(function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        $devices->setCollection($newDevices);

        return view('livewire.show-backups', [
            'devices' => $devices,
        ]);
    }

    #[On('delete')]
    public function delete($model)
    {
        $this->requireAdmin();
        DeviceBackup::where('id', $model)->delete();
    }
}
