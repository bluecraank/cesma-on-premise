<?php

namespace App\Livewire;

use App\Traits\NumberOfEntries;
use App\Traits\WithLogin;
use Livewire\Component;

class ShowNotifications extends Component
{
    use WithLogin;
    use NumberOfEntries;

    public $numberOfEntries = 50;

    public function render()
    {
        $notifications = \App\Models\Notification::latest('updated_at')->where('status', 'waiting')->take($this->numberOfEntries)->get();

        return view('livewire.show-notifications', [
            'notifications' => $notifications,
        ]);
    }
}
