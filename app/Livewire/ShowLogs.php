<?php

namespace App\Livewire;


use Livewire\Component;
use App\Traits\WithLogin;
use Livewire\WithPagination;
use App\Models\Log;
use App\Traits\NumberOfEntries;

class ShowLogs extends Component
{
    use WithLogin;
    use WithPagination;
    use NumberOfEntries;

    public $topic = "";
    public $numberOfEntries = 25;
    public $search = "";

    public function mount() {
        $this->checkLogin();
    }

    public function render()
    {
        return view('livewire.show-logs',[
            'logs' => Log::where(function($query) {
                $query->where('description', 'like', '%'.$this->search.'%');
            })->latest()->paginate($this->numberOfEntries ?? 25),
            'topics' => Log::select('category')->distinct()->get(),
        ]);
    }
}
