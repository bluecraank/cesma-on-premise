<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Portoverview extends Component
{
    public $device;
    public $ports;

    #[Renderless]
    public function save() {
        $class = config('app.types')[$this->device->type];

        if($login_info = $class::API_LOGIN($this->device)) {
            $this->dispatch('save_current_data', device_id: $this->device->id, cookie: Crypt::encrypt($login_info))->to(DevicePort::class);
        } else {
            $this->dispatch('notify-error', message: "API Login failed");
        }
    }

    #[Renderless]
    public function reset_ports() {
        $this->dispatch('reset_data')->to(DevicePort::class);
    }

    public function render()
    {
        return view('livewire.portoverview');
    }
}
