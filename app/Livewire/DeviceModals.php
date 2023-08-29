<?php

namespace App\Livewire;

use App\Models\Building;
use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;

class DeviceModals extends Component
{
    public $show = false;
    public Device $device;
    public $modal;

    public $rooms = [];

    #[Rule('required|min:3|max:255')]
    public $name;

    #[Rule('required|min:2|max:255|unique:devices,hostname')]
    public $hostname;

    #[Rule('required|numeric|exists:sites,id')]
    public $site_id;

    #[Rule('required|numeric|exists:buildings,id')]
    public $building_id;

    #[Rule('required|numeric|exists:rooms,id')]
    public $room_id;

    #[Rule('nullable|min:1|max:255')]
    public $description;

    #[Rule('required|min:4|max:255')]
    public $password = "__hidden__";

    #[Rule('required|in:aruba-os,aruba-cx,dell-emc,dell-emc-powerswitch')]
    public $type;

    #[On('show')]
    public function show($device, $modal)
    {
        $this->show = true;
        $this->modal = $modal;
        if($modal != "create") {
            $device = Device::where('id', $device)->first();

            if(!$device) {
                $this->dispatch('notify-error', message: __('Device not found'));
                return;
            }

            $this->device = $device;

            $this->name = $device->name;
            $this->hostname = $device->hostname;
            $this->site_id = $device->site_id;
            $this->building_id = $device->building_id;
            $this->room_id = $device->room_id;
            $this->description = $device->location_description;
        }
    }

    public function close() {
        $this->show = false;
    }

    public function delete() {
        $this->show = false;
        $id = $this->device->id;
        DeviceService::deleteDeviceData($id, $this->device->ports()->pluck('id')->toArray());
        $this->dispatch('delete', model: $id)->to(ShowDevices::class);
        $this->dispatch('notify-success', message: __('Device deleted'));
        $this->dispatch('refresh')->to(ShowDevices::class);
    }

    public function update()
    {
        $this->type = $this->device->type;

        $this->validate([
            'hostname' => 'required|min:2|max:255|unique:devices,hostname,'.$this->device->id,
            'name' => 'required|min:3|max:255',
            'site_id' => 'required|numeric|exists:sites,id',
            'building_id' => 'required|numeric|exists:buildings,id',
            'room_id' => 'required|numeric|exists:rooms,id',
            'description' => 'nullable|min:1|max:255',
            'password' => 'required|min:4|max:255',
            'type' => 'required|in:aruba-os,aruba-cx,dell-emc,dell-emc-powerswitch',
        ]);

        $this->device->name = $this->name;
        $this->device->hostname = $this->hostname;
        $this->device->site_id = $this->site_id;
        $this->device->building_id = $this->building_id;
        $this->device->room_id = $this->room_id;
        $this->device->location_description = $this->description;

        if($this->password != "__hidden__") {
            $this->device->password = Crypt::encrypt($this->password);
        }

        $this->device->save();
        $this->show = false;
        $this->dispatch('notify-success', message: __('Device updated'));
        $this->dispatch('refresh')->to(ShowDevices::class);
    }

    public function create() {
        $this->site_id = Auth::user()->currentSite()->id;

        $this->validate();


        $device = new Device();
        $device->name = $this->name;
        $device->hostname = $this->hostname;
        $device->site_id = $this->site_id;
        $device->building_id = $this->building_id;
        $device->room_id = $this->room_id;
        $device->location_description = $this->description;
        $device->password = Crypt::encrypt($this->password);
        $device->type = $this->type;

        if($device->save()) {
            $this->show = false;
            $this->dispatch('notify-success', message: __('Device created'));
            $this->dispatch('refresh')->to(ShowDevices::class);

            proc_open("php " . base_path() . "/artisan device:refresh " . $device->id . " snmp", [], $pipes, base_path());
        } else
        {
            $this->dispatch('notify-error', message: __('Device could not be created'));
        }
    }

    public function updateBuildingId() {
        $this->building_id = $this->building_id;
    }

    public function render()
    {
        if(isset($this->building_id)) {
            $this->rooms = Building::where('id', $this->building_id)->first()?->rooms ?? [];
        } else {
            $this->rooms = [];
        }

        $rooms = Building::where('id', $this->building_id)->first()?->rooms->pluck('id')->toArray() ?? [];
        if(!in_array($this->building_id, $rooms)) {
            $this->room_id = 0;
        }

        return view('modals.device.create-update-delete', [
            'show' => $this->show,
            'modal' => $this->modal,
            'device' => $this->device ?? null,
            'sites' => Auth::user()->availableSites(),
            'buildings' => Auth::user()->currentSite()->buildings,
            'rooms' => $this->rooms,
        ]);
    }
}
