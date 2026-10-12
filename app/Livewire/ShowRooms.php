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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $search = \App\Helper\Utilities::like($this->search);
        $buildings = Building::where('site_id', Auth::user()->currentSite()->id)->get();

        // Search in the room name and in the name of its building
        $rooms = Room::with('building')->whereIn('building_id', $buildings->pluck('id')->toArray())->where(function ($query) use ($search) {
            $query->where('name', 'like', $search)
                ->orWhereHas('building', fn ($building) => $building->where('name', 'like', $search));
        })->orderBy('name')->paginate($this->numberOfEntries ?? 25);

        // Sort rooms by name in natural order
        \App\Helper\Utilities::sortPageNaturally($rooms, 'name');


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
