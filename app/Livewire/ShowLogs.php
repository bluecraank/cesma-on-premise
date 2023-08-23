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
        return view('livewire.show-logs',[
            'logs' => Log::where('category', 'LIKE', $topic)->latest()->paginate($this->numberOfEntries ?? 25),
            'topics' => Log::select('category')->distinct()->get(),
        ]);
    }
}
