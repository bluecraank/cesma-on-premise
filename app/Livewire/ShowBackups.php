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

    public function render()
    {
        $backups = DeviceBackup::select('id', 'status', 'created_at', 'device_id')->get()->keyBy('id');
        $devices = Device::where('site_id', Auth::user()->currentSite()->id)->where('name', 'LIKE', '%'.$this->search.'%')->orderBy('name')->paginate($this->numberOfEntries ?? 25);

        foreach ($devices as $device) {
            $device->last_backup = $backups->where('device_id', $device->id)->last();
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
        DeviceBackup::where('id', $model)->delete();
    }
}
