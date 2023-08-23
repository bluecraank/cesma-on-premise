<?php

namespace App\Livewire;

use App\Models\DevicePort as ModelsDevicePort;
use App\Models\DeviceVlan;
use App\Services\DeviceService;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class DevicePort extends Component
{
    public $description;
    public $untagged = null;
    public $tagged = [];
    public $id;
    public $device_id;
    public $link;
    public $speed;
    public $name;
    public $clients;

    public $propertyUpdated = [];

    public $somethingChanged = false;

    public function updateProperty($property) {
        $this->propertyUpdated[$property] = true;

        $this->somethingChanged = true;

        if($property == "untagged") {
            $this->untagged = $this->untagged;
        } elseif($property == "description") {
            $this->description = $this->description;
        }
    }

    #[On('newTaggedVlans')]
    public function newTaggedVlans($port, $vlans) {
        if($port == $this->id) {
            $this->tagged = $vlans;
            $this->somethingChanged = true;
            $this->propertyUpdated['tagged'] = true;
        }
    }

    public function placeholder()
    {
        return <<<'HTML'
        <tr>
            <!-- Loading spinner... -->
            <td colspan="9" class="has-text-centered"><button class="is-white button is-loading"></button></td>
        </tr>
        HTML;
    }

    #[Renderless]
    public function openTagModal() {
        $this->dispatch('open', $this->id)->to(VlanTaggingModal::class);
    }

    public function mount($port) {
        $this->somethingChanged = false;
        $this->propertyUpdated = [];

        $curPort = ModelsDevicePort::find($port['id']);

        $this->id = $curPort->id;
        $this->description = $curPort->description;
        $this->untagged = $curPort->untagged->id ?? null;
        $this->tagged = $curPort->tagged->pluck('id')->toArray();
        $this->device_id = $curPort->device_id;
        $this->link = $curPort->link;
        $this->speed = $curPort->getSpeedAsTagAttribute();
        $this->name = $curPort->name;
        $this->clients = $curPort->clients_count();


    }

    #[Renderless]
    #[On('save_current_data')]
    public function save_current_data($device_id, $cookie) {
        $this->device_id = $device_id;
        $this->id = $this->id;
        $port = \App\Models\DevicePort::find($this->id);

        $cookie = Crypt::decrypt($cookie);

        if($this->somethingChanged) {
            if(isset($this->propertyUpdated['description'])) {
                self::updatePortDescription($cookie, $port);
            }

            if(isset($this->propertyUpdated['untagged'])) {
                self::updatePortUntaggedVlan($cookie, $port);
            }

            if(isset($this->propertyUpdated['tagged'])) {
                self::updatePortTaggedVlans($cookie, $port);
            }
        }
        $this->mount($port);
    }

    #[On('reset_data')]
    public function reset_data() {
        $port = \App\Models\DevicePort::find($this->id);
        $this->mount($port);
    }

    public function render()
    {
        $vlans = DeviceVlan::where('device_id', $this->device_id)->get();
        return view('livewire.device-port', ['vlans' => $vlans]);
    }

    public function updatePortDescription($cookie, $port) {
        if(DeviceService::updatePortDescription($cookie, $port, $this->device_id, $this->description)) {
            $this->dispatch('notify-success', message: __('Description of port :port changed', ['port' => $port->name]));
        } else {
            $this->dispatch('notify-error', message: __('Description of port :port could not be changed', ['port' => $port->name]));
        }
    }

    public function updatePortUntaggedVlan($cookie, $port) {
        if(DeviceService::updatePortUntaggedVlan($cookie, $port, $this->device_id, $this->untagged)) {
            $this->dispatch('notify-success', message: __('Untagged vlan of port :port changed', ['port' => $port->name]));
        } else {
            $this->dispatch('notify-error', message: __('Untagged vlan of port :port could not be changed', ['port' => $port->name]));
        }
    }

    public function updatePortTaggedVlans($cookie, $port) {
        // Vlans to add, Vlans to remove, Vlans successfully added, Vlans successfully removed
        $returnArrays = DeviceService::updatePortTaggedVlans($cookie, $port, $this->device_id, $this->tagged);

        if(count($returnArrays[0]) == count($returnArrays[2]) && count($returnArrays[1]) == count($returnArrays[3])) {
            $this->dispatch('notify-success', message: __('Tagged vlans of port :port changed', ['port' => $port->name]));
        } else {
            // Gewählte und erfolgreiche entfernte
            $count_all = count($returnArrays[0]) + count($returnArrays[1]);
            $count_successful = count($returnArrays[2]) + count($returnArrays[3]);

            $this->dispatch('notify-error', message: __('Only :count of :count_all vlans changed on port :port', ['count' => $count_successful, 'count_all' => $count_all, 'port' => $port->name]));
        }
    }
}
