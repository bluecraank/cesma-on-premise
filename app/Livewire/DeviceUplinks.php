<?php

namespace App\Livewire;

use App\Models\Device;
use App\Models\DeviceUplink;
use App\Models\DevicePort;
use Livewire\Component;

class DeviceUplinks extends Component
{
    public $uplinks;
    public $device_id;
    public $new_uplink;

    public function mount(Device $device)
    {
        $this->new_uplink = '';
        $this->uplinks = [];
        $this->device_id = $device->id;

        $found_uplinks = $device->uplinks->sort(function ($a, $b) {
            return strnatcmp($a->name, $b->name);
        })->keyBy('id')->toArray();

        foreach($found_uplinks as $id => $uplink) {
            $this->uplinks[$uplink['name']] = [];
            $this->uplinks[$uplink['name']]['id'] = $id;
            $this->uplinks[$uplink['name']]['members'] = $uplink['ports'] ?? $uplink['name'];

            if(is_array($this->uplinks[$uplink['name']]['members'])) {
                $this->uplinks[$uplink['name']]['members'] = implode(', ', $this->uplinks[$uplink['name']]['members']);
            }
        }
    }

    public function render()
    {
        return view('livewire.device-uplinks');
    }

    public function add() {
        $port = $this->new_uplink;

        if(DevicePort::where('name', $port)->where('device_id', $this->device_id)->exists()) {
            DeviceUplink::updateOrCreate([
                'name' => $port,
                'device_id' => $this->device_id,
                'device_port_id' => DevicePort::where('name', $port)->where('device_id', $this->device_id)->first()->id
            ]);
            $this->dispatch('notify-success', message: 'Uplink added');
            $this->mount(Device::where('id', $this->device_id)->first());
            return true;
        }

        $this->dispatch('notify-error', message: 'Port not found');
        return false;
    }

    public function delete($id) {
        $device = Device::where('id', $this->device_id)->first();

        if(!$device) {
            $this->dispatch('notify-error', message: 'Device not found');
        }

        $device->uplinks()->where('id', $id)->delete();
        $this->dispatch('notify-success', message: 'Uplink deleted');
        $this->mount($device);
    }
}
