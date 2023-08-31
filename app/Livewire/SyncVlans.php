<?php

namespace App\Livewire;

use App\Models\Device;
use App\Models\Vlan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
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

    public function mount() {
        if(Auth::user()->role <= 1) {
            abort(403);
        }
    }

    #[On('update')]
    public function update($vlans, $devices) {
        $this->selectedDevices = $devices;
        $this->selectedVlans = $vlans;


        // if($this->selectedVlans == null || count($this->selectedVlans) == 0) {
        //     $this->dispatch('notify-error', message: __('No vlans selected'));
        //     return;
        // }

        // if($this->selectedDevices == null || count($this->selectedDevices) == 0) {
        //     $this->dispatch('notify-error', message: __('No devices selected'));
        //     return;
        // }

        $this->hidePreparation = true;
        $this->someIsEmpty = false;

        foreach($this->selectedDevices as $device) {
            $selDevice = Device::where('id', $device)->first();
            $this->dispatch("sync-vlan-to-device", vlans: $this->selectedVlans, device: $selDevice->id, name: $selDevice->name, testmode: $this->testmode, createVlans: $this->createVlans, renameVlans: $this->renameVlans, tagToUplink: $this->tagToUplinks);
        }

    }

    #[On('start')]
    public function start() {
        $this->testmode = false;

        foreach($this->selectedDevices as $device) {
            $selDevice = Device::where('id', $device)->first();
            $this->dispatch("sync-vlan-to-device", vlans: $this->selectedVlans, device: $selDevice->id, name: $selDevice->name, testmode: $this->testmode, createVlans: $this->createVlans, renameVlans: $this->renameVlans, tagToUplink: $this->tagToUplinks);
        }
    }

    public function render()
    {
        $this->vlans = Vlan::where('site_id', Auth::user()->currentSite()->id)->where('is_synced', 1)->get()->toArray();

        $writeableTypes = array_filter(config('app.read-only'), function($type) {
            return $type == false;
        });
        $writeableTypes = array_keys($writeableTypes);

        $this->devices = Device::where('site_id', Auth::user()->currentSite()->id)->whereIn('type', $writeableTypes)->get()->toArray();

        return view('livewire.sync-vlans');
    }
}
