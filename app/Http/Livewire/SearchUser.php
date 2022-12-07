<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Traits\WithLogin;


class SearchUser extends Component
{
    use WithLogin;

    public $searchTerm = "";

    public function mount() {
        $this->checkLogin();
    } 

    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('livewire.search-user',[
            'users' => User::where('name','like', $searchTerm)->orWhere('email', 'like', $searchTerm)->get()->sortBy('name'),
        ]);
    }
}
