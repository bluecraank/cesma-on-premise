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
        $rooms = Room::paginate($this->numberOfEntries);
        $rooms->sortBy('name');

        $buildings = Building::where('site_id', Auth::user()->currentSite()->id)->get();

        return view('livewire.show-rooms', [
            'rooms' => $rooms,
            'buildings' => $buildings,
        ]);
    }
}
