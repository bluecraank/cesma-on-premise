<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Vlan;
use App\Traits\WithLogin;
use Livewire\WithPagination;


class SearchVlans extends Component
{
    use WithLogin;
    use WithPagination;

    public $searchTerm = "";

    public function mount()
    {
        $this->checkLogin();
    }

    public function render()
    {
        $searchTerm = '%' . $this->searchTerm . '%';

        $vlans = Vlan::where('name', 'like', $searchTerm)
        ->orWhere('vid', 'like', $searchTerm)
        ->orWhere('description', 'like', $searchTerm)
        ->paginate(20);

        $vlans->sort(function ($a, $b) {
            return strnatcmp($a->vid, $b->vid);
        });

        return view('vlan.vlan-overview-livew', [
            'vlans' => $vlans,
        ]);
    }
}
