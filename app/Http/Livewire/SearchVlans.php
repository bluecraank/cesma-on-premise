<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Vlan;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;


class SearchVlans extends Component
{
    use WithLogin;
    use WithPagination;

    public $searchTerm = "";
    public $numberOfEntries = 25;

    public function mount()
    {
        $this->checkLogin();
    }

    public function render()
    {
        $searchTerm = '%' . $this->searchTerm . '%';

        $vlans = Vlan::where('site_id', Auth::user()->currentSite()->id)->where(function ($query) use ($searchTerm) {
            $query->where('name', 'like', $searchTerm)
            ->orWhere('vid', 'like', $searchTerm)
            ->orWhere('description', 'like', $searchTerm);
        })->paginate($this->numberOfEntries);

        $vlans->sort(function ($a, $b) {
            return strnatcmp($a->vid, $b->vid);
        });

        return view('vlan.vlan-overview-livew', [
            'vlans' => $vlans,
        ]);
    }
}
