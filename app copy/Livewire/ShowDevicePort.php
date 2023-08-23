<?php

namespace App\Livewire;


use App\Models\Device;
use App\Services\DeviceService;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;
use App\Helper\CLog;
use App\Models\DevicePort;
use Livewire\Attributes\Renderless;

class ShowDevicePort extends Component
{

    use WithLogin;

    public $loaded = null;
    public $device_id;
    public $vlanports;
    public $port;
    public $vlans;
    public $clients;

    public $showTaggedModal = false;

    public $doNotDisable = false;
    public $untaggedVlanId;
    public $taggedVlans;

    public $somethingChanged = false;

    public $newTaggedVlans = [];
    public $taggedVlansUpdated = false;

    public $portDescription;
    public $portDescriptionUpdated = false;

    private $portId;

    public $readonly;

    public $newUntaggedVlan;
    public $untaggedVlanUpdated = false;

    protected $listeners = ['sendPortVlanUpdate'];

    public function mount()
    {
        $this->checkLogin();
        $this->portDescription = $this->port->description;
        $this->newTaggedVlans = [];
        $this->taggedVlansUpdated = false;
        $this->untaggedVlanUpdated = false;
        $this->doNotDisable = false;
        $this->somethingChanged = false;
        $this->portDescriptionUpdated = false;
        $this->loaded = true;
        $this->untaggedVlanId = $this->vlanports->where('is_tagged', false)->first()->device_vlan_id ?? 0;
    }

    public function render()
    {
        // $this->port = $this->port->with(['deviceVlanPorts'])->first();
        return view('livewire.show-port', ['port' => $this->port, 'readonly' => $this->readonly]);
    }

    public function prepareUntaggedVlan() {
        $this->untaggedVlanUpdated = true;
        $this->doNotDisable = true;
        $this->somethingChanged = true;
    }

    public function prepareTaggedVlans($portId, $vlans) {
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
                $new_model = DevicePort::where('id', $this->port->id)->first();

                // Untagged Vlan changes
                if($this->untaggedVlanUpdated) {
                    if($message['untagged']['success']) {
                        $this->dispatchBrowserEvent('notify-success', ['message' => __('Switch.Port.Untagged.Success', ['vlan' => $new_model->untagged->name, 'port' => $this->port->name]), 'portid' => $this->portId]);
                        $this->somethingChanged = false;
                        $this->fetchPort();
                        CLog::info("SwitchPort", __('Switch.Port.Untagged.Success', ['vlan' => $new_model->untagged->name, 'port' => $this->port->name]), $device, ($this->port->untaggedVlanName() ?? 'Keins') . " => " .$new_model->untagged->name );
                    } else {
                        $this->dispatchBrowserEvent('notify-error', ['message' => __('Switch.Port.Untagged.Error', ['vlan' => $this->port->untaggedVlanName(), 'port' => $this->port->name]), 'portid' => $this->portId]);
                        CLog::error("SwitchPort", __('Switch.Port.Untagged.Error', ['vlan' => $this->port->untaggedVlanName(), 'port' => $this->port->name]), $device, ($this->port->untaggedVlanName() ?? 'Keins') . " => " .$new_model->untagged->name );
                    }
                }

                // Tagged Vlan changes
                if($this->taggedVlansUpdated) {
                    // If vlans were removed from port
                    if(isset($message['tagged']['removed']) && count($message['tagged']['removed']) == $message['tagged']['count_remove']) {
                        $this->dispatchBrowserEvent('notify-success', ['message' => __('Switch.Port.Tagged.Removed.Success', ['success' => count($message['tagged']['removed']), 'total' => $message['tagged']['count_remove'], 'port' => $this->port->name]), 'portid' => $this->portId]);
                        $this->somethingChanged = false;
                        $this->fetchPort();
                        CLog::info("SwitchPort", __('Switch.Port.Tagged.Removed.Success', ['success' => count($message['tagged']['removed']), 'total' => $message['tagged']['count_remove'], 'port' => $this->port->name]), $device, implode(", ", array_keys($message['tagged']['removed'])));
                    } elseif(isset($message['tagged']['removed'])) {
                        $this->dispatchBrowserEvent('notify-error', ['message' => __('Switch.Port.Tagged.Removed.Error', ['port' => $this->port->name]), 'portid' => $this->portId]);
                        CLog::error("SwitchPort", __('Switch.Port.Tagged.Removed.Error', ['port' => $this->port->name]), $device, implode(", ", array_keys($message['tagged']['removed'])));
                    }

                    // If vlans were added to port
                    if(isset($message['tagged']['added']) && count($message['tagged']['added']) == $message['tagged']['count_add']) {
                        $this->dispatchBrowserEvent('notify-success', ['message' => __('Switch.Port.Tagged.Success', ['success' => count($message['tagged']['added']), 'total' => $message['tagged']['count_add'], 'port' => $this->port->name]), 'portid' => $this->portId]);
                        $this->somethingChanged = false;
                        $this->fetchPort();
                        CLog::info("SwitchPort", __('Switch.Port.Tagged.Success', ['success' => count($message['tagged']['added']), 'total' => $message['tagged']['count_add'], 'port' => $this->port->name]), $device, implode(", ", array_keys($message['tagged']['added'])));
                    } elseif(isset($message['tagged']['added'])) {
                        $this->dispatchBrowserEvent('notify-error', ['message' => __('Switch.Port.Tagged.Error', ['port' => $this->port->name]), 'portid' => $this->portId]);
                        CLog::error("SwitchPort", __('Switch.Port.Tagged.Error', ['port' => $this->port->name]), $device, implode(", ", array_keys($message['tagged']['added'])));
                    }
                }
            }

            // Port description changes
            if($this->portDescriptionUpdated) {
                $currentDescription = $this->port->description;
                if(DeviceService::updatePortDescription($raw_cookie, $this->port, $this->device_id, $this->portDescription)) {
                    $this->port->description = $this->portDescription;
                    $this->port->save();

                    $this->somethingChanged = false;
                    $this->fetchPort();

                    $this->dispatchBrowserEvent('notify-success', ['message' => __('Msg.ApiPortNameSet', ['port' => $this->port->name]), 'portid' => $this->portId]);
                    CLog::info("SwitchPort", __('Msg.ApiPortNameSet', ['port' => $this->port->name]), $device, $currentDescription . " => " . $this->port->description);
                } else {
                    $this->dispatchBrowserEvent('notify-error', ['message' => __('Msg.ApiPortNameSetError', ['port' => $this->port->name]), 'portid' => $this->portId]);
                }

                $this->portDescriptionUpdated = false;
                $this->somethingChanged = false;
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

    #[Renderless]
    public function openTagModal() {
        $this->dispatch('open', $this->port->id)->to(VlanTaggingModal::class);
    }
}
