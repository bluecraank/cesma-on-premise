<?php

namespace App\Livewire;

use App\Helper\CLog;
use App\Models\DevicePort;
use App\Models\DeviceUplink;
use App\Traits\NumberOfEntries;
use App\Traits\WithLogin;
use Livewire\Component;

class ShowNotifications extends Component
{
    use WithLogin;
    use NumberOfEntries;

    public $numberOfEntries = 50;

    public function accept($id) {
        $notification = \App\Models\Notification::find($id);

        if(!$notification) {
            $this->dispatch('notify-error', message: 'Notification not found');
        }


        $data = json_decode($notification->data, true);
        $port = DevicePort::where('device_id', $notification->device_id)->where('name', $data['port'])->first();
        $device = \App\Models\Device::find($notification->device_id);

        if(!$port) {
            $this->dispatch('notify-error', message: 'Port not found');
        }

        if(!$device) {
            $this->dispatch('notify-error', message: 'Device not found');
        }

        $notification->status = 'accepted';
        $notification->save();

        DeviceUplink::updateOrCreate([
            'name' => $data['port'],
            'device_id' => $notification->device_id,
            'device_port_id' => $port->id,
        ]);

        CLog::info("Device", "Added Port {$data['port']} as uplink for device {$device->name}");
        $this->dispatch('notify-success', message: "Added Port {$data['port']} as uplink for device {$device->name}");
    }

    public function decline($id) {
        $notification = \App\Models\Notification::find($id);

        if(!$notification) {
            $this->dispatch('notify-error', message: 'Notification not found');
        }

        $data = json_decode($notification->data, true);
        $port = DevicePort::where('device_id', $notification->device_id)->where('name', $data['port'])->first();
        $device = \App\Models\Device::find($notification->device_id);

        if(!$port) {
            $this->dispatch('notify-error', message: 'Port not found');
        }

        if(!$device) {
            $this->dispatch('notify-error', message: 'Device not found');
        }

        $notification->status = 'declined';
        $notification->save();

        CLog::info("Device", "Declined Port {$data['port']} as uplink for device {$device->name}");
        $this->dispatch('notify-success', message: "Declined Port {$data['port']} as uplink for device {$device->name}");
    }

    public function render()
    {
        $notifications = \App\Models\Notification::latest('updated_at')->where('status', 'waiting')->take($this->numberOfEntries)->get();

        return view('livewire.show-notifications', [
            'notifications' => $notifications,
        ]);
    }
}
