<?php

namespace App\Livewire;

use App\Helper\CLog;
use App\Models\Vlan;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;

class VlanModals extends Component
{
    public $show = false;
    public $vlan;
    public $modal;

    #[Rule('required|min:2|max:255')]
    public $name;

    #[Rule('nullable|min:8|max:255')]
    public $ip_range;

    #[Rule('nullable')]
    public $description;

    #[Rule('required|integer|exists:sites,id')]
    public $site_id;

    #[Rule('required|boolean')]
    public $is_client_vlan;

    #[Rule('required|boolean')]
    public $is_synced;


    #[On('show')]
    public function show($vlan, $modal)
    {
        $vlan = Vlan::where('id', $vlan)->first();

        if(!$vlan) {
            $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => __('Vlan not found')]);
            return;
        }

        $this->show = true;
        $this->modal = $modal;
        $this->vlan = $vlan;
        $this->name = $vlan->name;
        $this->ip_range = $vlan->ip_range;
        $this->description = $vlan->description;
        $this->site_id = $vlan->site_id;
        $this->is_client_vlan = $vlan->is_client_vlan;
        $this->is_synced = $vlan->is_synced;
    }

    public function close() {
        $this->show = false;
    }

    public function delete() {
        $this->show = false;
        $id = $this->vlan->id;
        $this->dispatch('delete', model: $id)->to(ShowVlans::class);
        $this->dispatch('notify-success', message: __('Vlan deleted'));
        $this->dispatch('refresh')->to(ShowVlans::class);
        CLog::info("Vlan", __('Vlan deleted', ['name' => $this->name]));
        $this->vlan = null;
        $this->name = null;
    }

    public function update()
    {
        $this->validate();

        $this->vlan->name = $this->name;
        $this->vlan->ip_range = $this->ip_range;
        $this->vlan->description = $this->description;
        $this->vlan->site_id = $this->site_id;
        $this->vlan->is_synced = $this->is_synced == "On" ? true : false;
        $this->vlan->is_client_vlan = $this->is_client_vlan == "On" ? true : false;
        $this->vlan->save();
        $this->show = false;
        $this->dispatch('notify-success', message: __('Vlan updated'));
        $this->dispatch('refresh')->to(ShowVlans::class);
        CLog::info("Vlan", __('Vlan :name updated', ['name' => $this->name]));
    }

    public function render()
    {
        return view('modals.vlan.update-delete', [
            'show' => $this->show,
            'modal' => $this->modal,
            'vlan' => $this->vlan ?? null,
        ]);
    }
}
