<?php

namespace App\Http\Livewire;

use App\Models\Device;
use App\Services\DeviceService;
use Livewire\Component;

class PortDetails extends Component
{

    public $device_id;
    public $port;
    public $vlans;
    public $doNotDisable = false;
    public $untaggedVlanId;

    public $somethingChanged = false;

    public $newTaggedVlans = [];
    public $taggedVlansUpdated = false;

    private $portId;

    public $newUntaggedVlan;
    public $untaggedVlansUpdated = false;

    protected $listeners = ['sendPortVlanUpdate', 'refreshComponent' => '$refresh'];

    public function mount()
    {
        $this->port = $this->port->fresh(); 
        $this->untaggedVlanId = $this->port->untaggedVlan();
        $this->newTaggedVlans = [];
        $this->taggedVlansUpdated = false;
        $this->untaggedVlansUpdated = false;
        $this->doNotDisable = false;
        $this->somethingChanged = false;
    }

    public function refreshComponent()
    {
        $this->port = $this->port->fresh(); 
        $this->mount();
    }

    public function render()
    {
        $ports = Device::find($this->device_id)->ports()->get()->sort(function($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        return view('livewire.port', compact('ports'));
    }

    public function prepareUntaggedVlan() {
        $this->untaggedVlansUpdated = true;
        $this->doNotDisable = true;
        $this->somethingChanged = true;
    }

    public function prepareTaggedVlans($portId, $componentId, $vlans) {
        $this->newTaggedVlans = $vlans;
        $this->doNotDisable = true;
        $this->portId = $portId;
        $this->taggedVlansUpdated = true;
        $this->somethingChanged = true;
    }

    public function sendPortVlanUpdate($cookie, $closeSession) {

        $raw_cookie = base64_decode($cookie);

        if($raw_cookie != "" && $this->untaggedVlansUpdated || $this->taggedVlansUpdated) {
            DeviceService::updatePortVlans($raw_cookie, $this->port, $this->device_id, $this->untaggedVlanId, $this->newTaggedVlans, $this->untaggedVlansUpdated, $this->taggedVlansUpdated);
            
            if($closeSession) {
                
                DeviceService::closeApiSession($raw_cookie, $this->device_id);
            }
            

            $this->mount();
            $this->dispatchBrowserEvent('notify-success', ['message' => "Sent to Job queue: VLANs on Port ". $this->port->name, 'portid' => $this->portId]);
        }

        return true;
    }
}
