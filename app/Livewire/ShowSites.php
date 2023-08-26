<?php

namespace App\Livewire;


use App\Models\Site;
use App\Traits\NumberOfEntries;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ShowSites extends Component
{
    use WithLogin;
    use WithPagination;
    use NumberOfEntries;

    public $numberOfEntries = 25;
    public $searchTerm;

    public function render()
    {
        $site = Site::where('name', 'LIKE', "%".$this->searchTerm."%")->where('id', Auth::user()->currentSite()->id)->firstOrFail();
        $sites = Site::orderBy('name')->paginate($this->numberOfEntries ?? 25);
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

    public function show($id, $modal)
    {
        $this->dispatch('show', site: $id, modal: $modal)->to(SiteModals::class);
    }

    #[On('refresh')]
    public function refresh()
    {
        $this->render();
    }

    #[On('delete')]
    public function delete($model)
    {
        Site::where('id', $model)->delete();
    }
}
