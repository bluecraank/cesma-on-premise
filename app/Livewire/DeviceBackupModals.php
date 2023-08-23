<?php

namespace App\Livewire;

use App\Helper\CLog;
use App\Models\DeviceBackup;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\On;
use Livewire\Component;


class DeviceBackupModals extends Component
{
    public $show = false;
    public DeviceBackup $backup;
    public $modal;


    #[On('show')]
    public function show($backup, $modal)
    {
        $this->backup = DeviceBackup::where('id', $backup)->first();

        if(!$this->backup) {
            $this->dispatch('notify-error', message: __('Backup not found'));
            return;
        }

        $this->modal = $modal;
        $this->show = true;
    }

    public function close() {
        $this->show = false;
    }

    #[On('download')]
    public function download($backup) {
        $backup = DeviceBackup::where('id', $backup)->first();

        $this->show = false;

        CLog::info("Backup", __('Backup :id downloaded', ['id' => $backup->id]), null, __('Device: :name, Backup created: :date', ['name' => $backup->device->name, 'date' => $backup->created_at]));

        return response()->streamDownload(function () use ($backup) {
            echo Crypt::decrypt($backup->data);
        }, $backup->device->name."-backup-{$backup->created_at}.txt");
    }

    public function delete() {
        $this->show = false;

        CLog::info("Backup", __('Backup :id deleted', ['id' => $this->backup->id]));

        $this->backup->delete();
        $this->backup = null;
        $this->dispatch('notify-success', message: __('Backup deleted'));
        $this->dispatch('refresh')->to(ShowDeviceBackups::class);
    }

    public function render()
    {
        return view('modals.device.backup.delete');
    }
}
