<?php

namespace App\Livewire;

use App\Helper\Utilities;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeviceExecuteSSH extends Component
{
    public $type;
    public $devices = [];
    public $command;

    public $collapsed = false;

    public function updateType()
    {
        $this->type = $this->type;
    }

    public function execute()
    {
        if (!$this->devices || count($this->devices) == 0) {
            $this->dispatch('notify-error', message: __('No devices selected'));
            return;
        }

        if(!$this->command || strlen($this->command) == 0) {
            $this->dispatch('notify-error', message: __('No command entered'));
            return;
        }

        if (!Utilities::CheckSSHCommand($this->command)) {
            $this->dispatch('notify-error', message: __('This ssh command is not allowed'));
        }

        foreach($this->devices as $device) {
            $this->dispatch('exec-ssh-command', type: $this->type, device: $device, command: $this->command, name: $device['hostname'], id: $device['id']);
        }

        $this->dispatch('notify-success', message: __('SSH command sent to :count devices', ['count' => count($this->devices)]));

        $this->collapsed = true;
    }

    public function render()
    {
        if (isset($this->type) && $this->type == 'by-device') {
            $this->devices = \App\Models\Device::where('site_id', Auth::user()->currentSite()->id)->get()->toArray();
            $this->dispatch('update');
        }

        return view('livewire.device-execute-ssh');
    }
}
