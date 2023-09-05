<?php

namespace App\Livewire;

use App\Helper\CLog;
use App\Models\Device;
use App\Models\DeviceBackup;
use App\Traits\NumberOfEntries;
use Livewire\Component;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Crypt;
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
        $backup = DeviceBackup::where('id', $id)->first();

        if(!$backup) {
            $this->dispatch('notify-error', message: __('Backup not found'));
            return;
        }

        CLog::info("Backup", __('Backup :id downloaded', ['id' => $backup->id]), null, __('Device: :name, Backup created: :date', ['name' => $backup->device->name, 'date' => $backup->created_at]));

        return response()->streamDownload(function () use ($backup) {
            echo Crypt::decrypt($backup->data);
        }, $backup->device->name."-backup-{$backup->created_at}.txt");
    }

    #[On('delete')]
    public function delete($id) {
        $backup = DeviceBackup::where('id', $id)->first();

        if(!$backup) {
            $this->dispatch('notify-error', message: __('Backup not found'));
            return;
        }

        CLog::info("Backup", __('Backup :id deleted', ['id' => $backup->id]), null, ['device' => $backup->device->name, 'backup' => $backup->created_at]);
        $backup->delete();
        $this->dispatch('notify-success', message: __('Backup deleted'));
        unset($backup);
    }

    #[On('refresh')]
    public function refresh()
    {
        $this->render();
    }
}
