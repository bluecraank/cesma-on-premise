<?php

namespace App\Http\Livewire;

use App\Models\Site;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ShowSites extends Component
{
    use WithLogin;
    use WithPagination;

    public $numberOfEntries = 25;

    public function render()
    {
        $site = Site::where('id', Auth::user()->currentSite()->id)->firstOrFail();
        $sites = Site::paginate($this->numberOfEntries);
        $sites->sortBy('name');

        // Sort sites by name in natural order
        $sites->sort(function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });
        
        return view('livewire.show-sites', [
            'site' => $site,
            'sites' => $sites,
        ]);
    }
}
