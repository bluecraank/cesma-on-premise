<?php

namespace App\Livewire;


use Livewire\Component;
use App\Traits\WithLogin;
use Livewire\WithPagination;
use App\Models\Log;
use App\Traits\NumberOfEntries;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;

class ShowLogs extends Component
{
    use WithLogin;
    use WithPagination;
    use NumberOfEntries;

    public $numberOfEntries = 25;

    #[Url]
    public $search = "";

    #[Url]
    public $level = "";

    #[Url]
    public $category = "";

    public function mount() {
        $this->checkLogin();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingLevel()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $site_id = Auth::user()->currentSite()->id;
        $isAdmin = Auth::user()->role >= 1;

        $logs = Log::where(function ($query) use ($site_id, $isAdmin) {
            // Entries of the current site, entries without site (login, system) only for admins
            $query->where('site_id', $site_id);
            if ($isAdmin) {
                $query->orWhereNull('site_id');
            }
        });

        if ($this->search !== "") {
            $search = '%' . $this->search . '%';
            $logs->where(function ($query) use ($search) {
                $query->where('description', 'like', $search)
                    ->orWhere('device_name', 'like', $search)
                    ->orWhere('user', 'like', $search)
                    ->orWhere('additional_info', 'like', $search);
            });
        }

        if ($this->level !== "") {
            $logs->where('level', $this->level);
        }

        if ($this->category !== "") {
            $logs->where('category', $this->category);
        }

        return view('livewire.show-logs',[
            'logs' => $logs->latest()->orderByDesc('id')->paginate($this->numberOfEntries ?? 25),
            'categories' => Log::select('category')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }
}
