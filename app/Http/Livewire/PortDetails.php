<?php

namespace App\Http\Livewire;

use App\Http\Controllers\ClientController;
use App\Models\Device;
use App\Services\ClientService;
use App\Services\DeviceService;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;

class PortDetails extends Component
{

    public $device_id;
    public $vlanports;
    public $port;
    public $vlans;
    public $clients;
    
    public $doNotDisable = false;
    public $untaggedVlanId;
    public $taggedVlans;
    public $cc;

    public $somethingChanged = false;

    public $newTaggedVlans = [];
    public $taggedVlansUpdated = false;

    public $portDescription;
    public $portDescriptionUpdated = false;

    private $portId;

    public $newUntaggedVlan;
    public $untaggedVlanUpdated = false;

    protected $listeners = ['sendPortVlanUpdate'];

    public function mount()
    {
        $this->cc = ClientService::class;
        $this->untaggedVlanId = $this->vlanports->where('is_tagged', false)->first()->device_vlan_id ?? 0;
        $this->taggedVlans = $this->vlanports->where('is_tagged', true);
        $this->newTaggedVlans = [];
        $this->taggedVlansUpdated = false;
        $this->untaggedVlanUpdated = false;
        $this->doNotDisable = false;
        $this->somethingChanged = false;
        $this->portDescription = $this->port->description;
        $this->portDescriptionUpdated = false;
    }

    public function render()
    {
        return view('livewire.port', ['port' => $this->port]);
    }

    public function prepareUntaggedVlan() {
        $this->untaggedVlanUpdated = true;
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

    public function preparePortDescription() {  
        $this->portDescriptionUpdated = true;
        $this->doNotDisable = true;
        $this->somethingChanged = true;
    }

    public function sendPortVlanUpdate($cookie, $closeSession) {

        $raw_cookie = Crypt::decrypt($cookie);

        if($raw_cookie != "" && $this->untaggedVlanUpdated || $this->taggedVlansUpdated || $this->portDescriptionUpdated) {
            
            if($this->untaggedVlanUpdated || $this->taggedVlansUpdated) {
                DeviceService::updatePortVlans($raw_cookie, $this->port, $this->device_id, $this->untaggedVlanId, $this->newTaggedVlans, $this->untaggedVlanUpdated, $this->taggedVlansUpdated);
                $this->dispatchBrowserEvent('notify-success', ['message' => "Port " . $this->port->name ." aktualisiert!", 'portid' => $this->portId]);

            }
 
            if($this->portDescriptionUpdated) {
                if(DeviceService::updatePortDescription($raw_cookie, $this->port, $this->device_id, $this->portDescription)) {
                    $this->port->description = $this->portDescription;
                    $this->port->save();

                    if($this->port->description == "" || $this->port->description == null) {
                        $this->dispatchBrowserEvent('notify-success', ['message' => "Port " . $this->port->name . " Name entfernt", 'portid' => $this->portId]);

                    }
                    else {
                        $this->dispatchBrowserEvent('notify-success', ['message' => "Port " . $this->port->name . " zu \"". $this->port->description ."\" geändert!", 'portid' => $this->portId]);

                    }
                }
            }

            if($closeSession) {
                DeviceService::closeApiSession($raw_cookie, $this->device_id);
            }

            $this->port = $this->port->fresh(); 
            $this->mount();
            $this->doNotDisable = true;

        }

        return true;
    }
}
