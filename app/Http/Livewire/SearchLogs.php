<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Traits\WithLogin;
use danielme85\LaravelLogToDB\LogToDB;


class SearchLogs extends Component
{
    use WithLogin;

    public $searchTerm = "";

    public function mount() {
        $this->checkLogin();
    } 

    public function render()
    {
        $searchTerm = '%'.$this->searchTerm.'%';
        return view('system.view_logs_livewire',[
            // 'logs' => Log::where('user','like', $searchTerm)->orWhere('data', 'like', $searchTerm)->orWhere('message', 'like', $searchTerm)->get()->sortByDesc('created_at'),
            'logs' => LogToDB::model()->get()
        ]);
    }
}
