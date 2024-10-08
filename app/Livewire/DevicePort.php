<?php

namespace App\Livewire;

use App\Helper\CLog;
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
    public $port_clients;
    public $propertyUpdated = [];
    public $vlan_mode;

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
        $this->port_clients = $curPort->clients()->toArray();
        $this->vlan_mode = $curPort->vlan_mode;
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
            CLog::info("DevicePort", "Description of port {$port->name} changed", null, "Old: \"{$port->description}\" New: \"{$this->description}\"");
        } else {
            $this->dispatch('notify-error', message: __('Description of port :port could not be changed', ['port' => $port->name]));
        }
    }

    public function updatePortUntaggedVlan($cookie, $port) {
        $temp = $port->untagged->id ?? '0';
        $vlan = DeviceVlan::whereId($temp)->first() ?? new DeviceVlan();
        $vlan->name = $vlan->name ?? 'None';

        $newVlan = DeviceVlan::whereId($this->untagged)->first() ?? new DeviceVlan();
        $newVlan->name = $newVlan->name ?? 'None';

        if(DeviceService::updatePortUntaggedVlan($cookie, $port, $this->device_id, $this->untagged)) {
            $this->dispatch('notify-success', message: __('Untagged vlan of port :port changed to :new', ['port' => $port->name, 'new' => $newVlan?->name]));
            CLog::info("DevicePort", "Untagged vlan of port {$port->name} changed", $port->device, "Old vlan: {$vlan?->name} New vlan: {$newVlan?->name}");
        } else {
            $this->dispatch('notify-error', message: __('Untagged vlan of port :port could not be changed', ['port' => $port->name]));
        }
    }

    public function updatePortTaggedVlans($cookie, $port) {
        // Vlans to add, Vlans to remove, Vlans successfully added, Vlans successfully removed
        $returnArrays = DeviceService::updatePortTaggedVlans($cookie, $port, $this->device_id, $this->tagged);

        if(count($returnArrays[0]) == count($returnArrays[2]) && count($returnArrays[1]) == count($returnArrays[3])) {
            $this->dispatch('notify-success', message: __('Tagged vlans of port :port changed', ['port' => $port->name]));
            CLog::info("DevicePort", "Tagged vlans of port {$port->name} changed", $port->device, "Added vlans: " . count($returnArrays[2]). ", Removed vlans: ". count($returnArrays[3]));
        } else {
            // Gewählte und erfolgreiche entfernte
            $count_all = count($returnArrays[0]) + count($returnArrays[1]);
            $count_successful = count($returnArrays[2]) + count($returnArrays[3]);
            CLog::warning("DevicePort", "Tagged vlans of port {$port->name} changed", $port->device, "Added vlans: " . $count_successful. ", Expected vlans: ". $count_all);
            $this->dispatch('notify-error', message: __('Only :count of :count_all vlans changed on port :port', ['count' => $count_successful, 'count_all' => $count_all, 'port' => $port->name]));
        }
    }
}
