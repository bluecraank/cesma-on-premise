<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;

class SearchUser extends Component
{
    public $searchTerm;
    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('livewire.search-user',[
            'users' => User::where('name','like', $searchTerm)->orWhere('email', 'like', $searchTerm)->get()
        ]);
    }
}
