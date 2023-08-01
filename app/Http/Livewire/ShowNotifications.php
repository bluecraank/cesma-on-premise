<?php

namespace App\Http\Livewire;

use App\Traits\WithLogin;
use Livewire\Component;
use Livewire\WithPagination;

class ShowNotifications extends Component
{
    use WithLogin;
    
    public $numberOfEntries = 50;

    public function render()
    {   
        $notifications = \App\Models\Notification::take($this->numberOfEntries)->latest()->get();

        return view('livewire.show-notifications', [
            'notifications' => $notifications,
        ]);
    }
}
