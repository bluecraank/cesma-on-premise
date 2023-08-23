<?php

namespace App\Livewire;


use App\Models\Device;
use App\Traits\NumberOfEntries;
use Livewire\Component;
use App\Traits\WithLogin;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class ShowDeviceBackups extends Component
{
    use WithLogin;
    use WithPagination;
    use NumberOfEntries;

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
        $backups = $device->backups()->latest()->paginate($this->numberOfEntries ?? 25);

        return view('livewire.show-device-backups', [
            'backups' => $backups,
            'device' => $device,
        ]);
    }

    public function show($id, $modal)
    {
        $this->dispatch('show', backup: $id, modal: $modal)->to(DeviceBackupModals::class);
    }

    public function download($id)
    {
        $this->dispatch('download', backup: $id)->to(DeviceBackupModals::class);
    }

    #[On('refresh')]
    public function refresh()
    {
        $this->render();
    }
}
