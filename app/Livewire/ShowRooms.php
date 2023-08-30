<?php

namespace App\Livewire;


use App\Models\Building;
use App\Models\Room;
use App\Traits\NumberOfEntries;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ShowRooms extends Component
{
    use WithLogin;
    use WithPagination;
    use NumberOfEntries;

    public $numberOfEntries = 25;
    public $search;

    public function render()
    {
        $buildings = Building::where('name', 'LIKE', "%".$this->search."%")->where('site_id', Auth::user()->currentSite()->id)->get();
        $rooms = Room::orderBy('name')->whereIn('building_id', $buildings->pluck('id')->toArray())->paginate($this->numberOfEntries ?? 25);
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

    public function show($id, $modal)
    {
        $this->dispatch('show', room: $id, modal: $modal)->to(RoomModals::class);
    }

    #[On('refresh')]
    public function refresh()
    {
        $this->render();
    }

    #[On('delete')]
    public function delete($model)
    {
        Room::where('id', $model)->delete();
    }
}
