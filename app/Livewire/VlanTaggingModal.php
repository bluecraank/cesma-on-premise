<?php

namespace App\Livewire;

use App\Models\DevicePort as ModelsDevicePort;
use App\Models\DeviceVlan;
use Livewire\Attributes\On;
use Livewire\Component;

class VlanTaggingModal extends Component
{

    public $showModal = false;
    public $port;
    public $name;
    public $device_id;
    public $vlans = [];
    public $taggedVlans = [];

    public $nameLabel = 1; // 1 = name, 2 = vid

    public function changeNameLabel($labelMode) {
        $this->nameLabel = $labelMode;
    }

    public function updateTaggedVlans($id) {
        if(isset($this->taggedVlans[$id])) {
            unset($this->taggedVlans[$id]);
            return;
        }

        $this->taggedVlans[$id] = true;
    }

    public function submitToPort() {
        $this->dispatch('newTaggedVlans', $this->port, $this->taggedVlans)->to(DevicePort::class);
        $this->showModal = false;
    }

    #[On('open')]
    public function open($port) {
        $this->port = $port;

        $port = ModelsDevicePort::where('id', $port)->first();
        $this->device_id = $port->device_id;

        $this->taggedVlans = $port->taggedVlans()->keyBy('device_vlan_id')->toArray();

        $this->name = $port->name;
        $this->showModal = true;
    }

    public function render()
    {
        $this->vlans = DeviceVlan::where('device_id', $this->device_id)->get()->sortBy('vlan_id')->toArray();

        return view('livewire.vlan-tagging-modal');
    }
}
