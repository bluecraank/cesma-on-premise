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
use App\Models\DevicePort;
use App\Helper\Diff;

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
        
        // Cache model to compare later
        $old_model = $this->port->attributesToArray();

        $raw_cookie = Crypt::decrypt($cookie);

        if($raw_cookie != "" && $this->untaggedVlanUpdated || $this->taggedVlansUpdated || $this->portDescriptionUpdated) {
            
            if($this->untaggedVlanUpdated || $this->taggedVlansUpdated) {
                $message = DeviceService::updatePortVlans($raw_cookie, $this->port, $this->device_id, $this->untaggedVlanId, $this->newTaggedVlans, $this->untaggedVlanUpdated, $this->taggedVlansUpdated);
                $vlans = DeviceVlan::where('device_id', $this->device_id);

                // Get new model to compare
                $new_model = DevicePort::where('id', $this->port->id)->first();
                $diff_model = Diff::compare($old_model, $new_model->attributesToArray());

                if($this->untaggedVlanUpdated) {
                    if($message['untagged']['success']) {
                        $this->dispatchBrowserEvent('notify-success', ['message' => __('Switch.Port.Untagged.Success', ['vlan' => $new_model->untagged->name, 'port' => $this->port->name]), 'portid' => $this->portId]);
                    } else {
                        $this->dispatchBrowserEvent('notify-error', ['message' => __('Switch.Port.Untagged.Error', ['vlan' => $this->port->untaggedVlanName(), 'port' => $this->port->name]), 'portid' => $this->portId]);
                    }

                    CLog::info("SwitchPort", "Set untagged vlan for port " . $this->port->name . " to " . $vlans->where('id', $this->untaggedVlanId)->first()->name, $device, $diff_model);
                }

                if($this->taggedVlansUpdated) {
                    $success = $error = 0;
                    foreach($message['tagged'] as $vlan) {
                        if($vlan['success']) {
                            $success++;
                        } else {
                            $error++;
                        }
                    }

                    if($success > 0) {
                        $this->dispatchBrowserEvent('notify-success', ['message' => __('Switch.Port.Tagged.Success', ['success' => $success, 'total' => $success+$error, 'port' => $this->port->name]), 'portid' => $this->portId]);
                    } else {
                        $this->dispatchBrowserEvent('notify-error', ['message' => __('Switch.Port.Tagged.Error', ['port' => $this->port->id]), 'portid' => $this->portId]);
                    }

                    $tagged_vlans = $vlans->whereIn('id', $this->newTaggedVlans)->get()->pluck('name')->toArray();
                    CLog::info("SwitchPort", "Set tagged vlans for port " . $this->port->name . " to " . implode(", ", $tagged_vlans), $device, $diff_model);
                }
            }
 
            if($this->portDescriptionUpdated) { 
                $currentDescription = $this->port->description;
                if(DeviceService::updatePortDescription($raw_cookie, $this->port, $this->device_id, $this->portDescription)) {   
                    $this->port->description = $this->portDescription;
                    $this->port->save();

                    $this->dispatchBrowserEvent('notify-success', ['message' => __('Msg.ApiPortNameSet', ['port' => $this->port->name]), 'portid' => $this->portId]);
                    CLog::info("SwitchPort", "Updated description of port {$this->port->name}", $device, Diff::compare($old_model, $this->port->attributesToArray()));
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
