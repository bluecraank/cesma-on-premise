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
        $notifications = \App\Models\Notification::take($this->numberOfEntries)->latest()->get();

        return view('livewire.show-notifications', [
            'notifications' => $notifications,
        ]);
    }
}
