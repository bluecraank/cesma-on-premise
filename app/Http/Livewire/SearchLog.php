<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Log;
use App\Traits\WithLogin;


class SearchLog extends Component
{
    use WithLogin;

    public $searchTerm = "";

    public function mount() {
        $this->checkLogin();
    } 

    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('livewire.search-log',[
            'logs' => Log::where('user','like', $searchTerm)->orWhere('data', 'like', $searchTerm)->orWhere('message', 'like', $searchTerm)->get()->sortByDesc('created_at'),
        ]);
    }
}
