<?php

namespace App\Livewire;

use App\Helper\CLog;
use App\Models\Building;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;

class BuildingModals extends Component
{

    public $show = false;
    public Building $building;

    #[Rule('required|min:3|max:255')]
    public $name;

    public $modal;

    #[On('show')]
    public function show($building, $modal)
    {
        $building = Building::find($building);
        $this->show = true;
        $this->modal = $modal;
        $this->building = $building;

        $this->name = $building->name;
    }

    public function close() {
        $this->show = false;
    }

    public function delete() {
        $this->show = false;
        if($this->building->devices()->count() >= 1) {
            $this->dispatch('notify-error', message: __('You cannot delete a building with devices'));
            return;
        }

        CLog::info("Building", __('Building :id deleted', ['id' => $this->building->id]), null, $this->building->name);

        $this->dispatch('delete', $this->building->id);
        $this->dispatch('notify-success', message: __('Building deleted'));
        $this->dispatch('refresh')->to(ShowBuildings::class);
    }

    public function update()
    {
        $this->validate();

        $temp = $this->building->name;
        $this->building->name = $this->name;
        $this->building->save();
        $this->show = false;
        $this->dispatch('notify-success', message: __('Building updated'));
        $this->dispatch('refresh')->to(ShowBuildings::class);
        CLog::info("Building", __('Building :id updated', ['id' => $this->building->id]), null, "{$temp} => {$this->name}");
    }


    public function render()
    {
        return view('modals.building.update-delete', [
            'show' => $this->show,
            'building' => $this->building ?? null
        ]);
    }
}
