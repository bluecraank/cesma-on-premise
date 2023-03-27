<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Traits\WithLogin;
use Livewire\WithPagination;
use App\Models\Log;


class SearchLogs extends Component
{
    use WithLogin;
    use WithPagination;

    public $topic = "";

    public function mount() {
        $this->checkLogin();
    } 

    public function render()
    {
        if($this->topic == "Port") {
            $topic = "Port";
        } else {
            $topic = '%'.$this->topic.'%';
        }
        return view('system.view_logs_livewire',[
            'logs' => Log::where('category', 'LIKE', $topic)->latest()->paginate(100),
            'topics' => Log::select('category')->distinct()->get(),
        ]);
    }
}
