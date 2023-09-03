<?php

namespace App\Livewire;

use App\Helper\CLog;
use App\Models\Room;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;

class RoomModals extends Component
{

    public $show = false;
    public $room;

    #[Rule('required|min:3|max:255')]
    public $name;

    public $modal;

    #[On('show')]
    public function show($room, $modal)
    {
        $room = Room::find($room);

        if(!$room) {
            $this->dispatch('notify-error', message: __('Room not found'));
            return;
        }

        $this->show = true;
        $this->modal = $modal;
        $this->room = $room;

        $this->name = $room->name;
    }

    public function close() {
        $this->show = false;
    }

    public function delete() {
        $this->show = false;
        if($this->room->devices()->count() >= 1) {
            $this->dispatch('notify-error', message: __('You cannot delete a room with devices'));
            return;
        }

        CLog::info("Room", __('Room :name deleted', ['name' => $this->room->name]), null, $this->room->name);

        $this->dispatch('delete', $this->room->id);
        $this->dispatch('notify-success', message: __('Room deleted'));
        $this->dispatch('refresh')->to(ShowRooms::class);

        $this->room = null;
        $this->name = null;
    }

    public function update()
    {
        $this->validate();

        $temp = $this->room->name;
        $this->room->name = $this->name;
        $this->room->save();
        $this->show = false;
        $this->dispatch('notify-success', message: __('Room updated'));
        $this->dispatch('refresh')->to(ShowRooms::class);
        CLog::info("Room", __('Room :name updated', ['name' => $temp]), null, "{$temp} => {$this->room->name}");
    }


    public function render()
    {
        return view('modals.room.update-delete', [
            'show' => $this->show,
            'room' => $this->room ?? null,
        ]);
    }
}
