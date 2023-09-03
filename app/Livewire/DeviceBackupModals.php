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
    public $backup;
    public $modal;
    public $id;
    public $created_at;

    #[On('show')]
    public function show($backup, $modal)
    {
        $this->backup = DeviceBackup::where('id', $backup)->first();

        if(!$this->backup) {
            $this->dispatch('notify-error', message: __('Backup not found'));
            return;
        }

        $this->id = $this->backup->id;
        $this->created_at = $this->backup->created_at;


        $this->modal = $modal;
        $this->show = true;
    }

    public function close() {
        $this->show = false;
    }

    public function delete() {
        $this->show = false;
        $this->dispatch('delete', id: $this->backup->id)->to(ShowDeviceBackups::class);
        $this->backup = null;
        $this->id = null;
        $this->created_at = null;
    }

    public function render()
    {
        return view('modals.device.backup.delete');
    }
}
