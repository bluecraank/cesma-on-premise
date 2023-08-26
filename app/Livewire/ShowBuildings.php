<?php

namespace App\Livewire;


use App\Models\Building;
use App\Traits\NumberOfEntries;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ShowBuildings extends Component
{
    use WithLogin;
    use WithPagination;
    use NumberOfEntries;

    public $numberOfEntries = 25;
    public $searchTerm;

    public function render()
    {
        // dd($this->searchTerm);
        $buildings = Building::orderBy('name')->where('name', 'LIKE', "%".$this->searchTerm."%")->where('site_id', Auth::user()->currentSite()->id)->paginate($this->numberOfEntries ?? 25);
        $buildings->sortBy('name');

        // Sort buildings by name in natural order
        $buildings->sort(function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        return view('livewire.show-buildings', [
            'buildings' => $buildings,
        ]);
    }

    public function show($id, $modal)
    {
        $this->dispatch('show', building: $id, modal: $modal)->to(BuildingModals::class);
    }

    #[On('refresh')]
    public function refresh()
    {
        $this->render();
    }

    #[On('delete')]
    public function delete($model)
    {
        Building::where('id', $model)->delete();
    }
}
