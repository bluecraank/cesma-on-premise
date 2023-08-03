<?php

namespace App\Http\Livewire;

use App\Models\Building;
use App\Models\Room;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ShowRooms extends Component
{
    use WithLogin;
    use WithPagination;

    public $numberOfEntries = 25;

    public function render()
    {
        $buildings = Building::where('site_id', Auth::user()->currentSite()->id)->get();
        $rooms = Room::orderBy('vid')->whereIn('building_id', $buildings->pluck('id')->toArray())->paginate($this->numberOfEntries);
        $rooms->sortBy('name');

        // Sort rooms by name in natural order
        $rooms->sort(function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });


        return view('livewire.show-rooms', [
            'rooms' => $rooms,
            'buildings' => $buildings,
        ]);
    }
}
