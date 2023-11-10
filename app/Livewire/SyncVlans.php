<?php

namespace App\Livewire;

use App\Models\Device;
use App\Models\Vlan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class SyncVlans extends Component
{
    public $someIsEmpty = true;

    public $vlans;
    public $devices;

    public $selectedVlans = [];
    public $selectedDevices = [];

    public $testmode = true;
    public $hidePreparation = false;

    public $createVlans = true;
    public $renameVlans = true;
    public $tagToUplinks = false;
    public $deleteVlans = false;

    public function mount() {
        if(Auth::user()->role <= 1) {
            abort(403);
        }
    }

    #[On('update')]
    #[Renderless]
    public function update($vlans, $devices) {
        $this->selectedDevices = $devices;
        $this->selectedVlans = $vlans;

        $this->someIsEmpty = false;

        if($this->deleteVlans && ($this->createVlans || $this->renameVlans || $this->tagToUplinks)) {
            $this->dispatch('notify-error', message: 'You can\'t delete and create/rename/tag at the same time. Action cancelled.');
            return false;
        }

        $this->hidePreparation = true;

        foreach($this->selectedDevices as $device) {
            $selDevice = Device::where('id', $device)->first();
            $this->dispatch("sync-vlan-to-device", vlans: $this->selectedVlans, device: $selDevice->id, name: $selDevice->name, testmode: $this->testmode, createVlans: $this->createVlans, renameVlans: $this->renameVlans, tagToUplink: $this->tagToUplinks, deleteVlans: $this->deleteVlans);
        }

    }

    #[On('start')]
    public function start() {
        $this->testmode = false;

        foreach($this->selectedDevices as $device) {
            $selDevice = Device::where('id', $device)->first();
            $this->dispatch("sync-vlan-to-device", vlans: $this->selectedVlans, device: $selDevice->id, name: $selDevice->name, testmode: $this->testmode, createVlans: $this->createVlans, renameVlans: $this->renameVlans, tagToUplink: $this->tagToUplinks, deleteVlans: $this->deleteVlans);
        }
    }

    public function render()
    {
        $this->vlans = Vlan::where('site_id', Auth::user()->currentSite()->id)->where('is_synced', 1)->get()->toArray();
        array_multisort(array_column($this->vlans, 'name'), SORT_NATURAL, $this->vlans);

        $writeableTypes = array_filter(config('app.read-only'), function($type) {
            return $type == false;
        });
        $writeableTypes = array_keys($writeableTypes);

        $this->devices = Device::where('site_id', Auth::user()->currentSite()->id)->whereIn('type', $writeableTypes)->get()->toArray();
        array_multisort(array_column($this->devices, 'name'), SORT_NATURAL, $this->devices);

        return view('livewire.sync-vlans');
    }
}
