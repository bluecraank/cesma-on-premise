<?php

namespace App\Http\Livewire;

use App\Models\Building;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ShowBuildings extends Component
{
    use WithLogin;
    use WithPagination;

    public $numberOfEntries = 25;

    public function render()
    {
        $buildings = Building::orderBy('vid')->where('site_id', Auth::user()->currentSite()->id)->paginate($this->numberOfEntries);
        $buildings->sortBy('name');

        // Sort buildings by name in natural order
        $buildings->sort(function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        return view('livewire.show-buildings', [
            'buildings' => $buildings,
        ]);
    }
}
