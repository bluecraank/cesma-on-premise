<?php

namespace App\Http\Livewire;

use App\Http\Controllers\ClientController;
use App\Models\Device;
use App\Services\ClientService;
use App\Services\DeviceService;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;
use App\Helper\CLog;
use Illuminate\Support\Facades\Auth;	
use App\Models\DeviceVlan;

class Port extends Component
{

    use WithLogin;

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
        $this->checkLogin();
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

        $device = Device::where('id', $this->device_id)->first();

        $raw_cookie = Crypt::decrypt($cookie);

        if($raw_cookie != "" && $this->untaggedVlanUpdated || $this->taggedVlansUpdated || $this->portDescriptionUpdated) {
            
            if($this->untaggedVlanUpdated || $this->taggedVlansUpdated) {
                $message = DeviceService::updatePortVlans($raw_cookie, $this->port, $this->device_id, $this->untaggedVlanId, $this->newTaggedVlans, $this->untaggedVlanUpdated, $this->taggedVlansUpdated);
                
                $vlans = DeviceVlan::where('device_id', $this->device_id);
                if($this->untaggedVlanUpdated) {
                    CLog::info("SwitchPort", "Set untagged vlan for port " . $this->port->name . " to " . $vlans->where('id', $this->untaggedVlanId)->first()->name, $device, "Port {$this->port->name}");
                }

                if($this->taggedVlansUpdated) {
                    $tagged_vlans = $vlans->whereIn('id', $this->newTaggedVlans)->get()->pluck('name')->toArray();
                    CLog::info("SwitchPort", "Set tagged vlans for port " . $this->port->name . " to " . implode(", ", $tagged_vlans), $device, "Port {$this->port->name}");
                }

                $this->dispatchBrowserEvent('notify-success', ['message' => $message, 'portid' => $this->portId]);
            }
 
            if($this->portDescriptionUpdated) { 
                $currentDescription = $this->port->description;
                if(DeviceService::updatePortDescription($raw_cookie, $this->port, $this->device_id, $this->portDescription)) {   
                    $this->port->description = $this->portDescription;
                    $this->port->save();

                    if($this->port->description == "" || $this->port->description == null) {
                        $this->dispatchBrowserEvent('notify-success', ['message' => __('Msg.ApiPortNameSet', ['port' => $this->port->name]), 'portid' => $this->portId]);
                        
                        CLog::info("SwitchPort", "Removed port description \"{$currentDescription}\"", $device, "Port {$this->port->name}");
                    }
                    else {
                        $this->dispatchBrowserEvent('notify-success', ['message' => __('Msg.ApiPortNameSet', ['port' => $this->port->name]), 'portid' => $this->portId]);
                        

                        CLog::info("SwitchPort", "Set port description from \"{$currentDescription}\" to \"{$this->port->description}\"", $device, "Port {$this->port->name}");
                    }
                } else {
                    $this->dispatchBrowserEvent('notify-error', ['message' => __('Msg.ApiPortNameSetError', ['port' => $this->port->name]), 'portid' => $this->portId]);
                    
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
