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
    public $search;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Only sites the user has access to, filtered by the search
        $sites = Site::whereIn('id', Auth::user()->availableSites()->pluck('id'))
            ->where('name', 'like', \App\Helper\Utilities::like($this->search))
            ->orderBy('name')
            ->paginate($this->numberOfEntries ?? 25);

        // Sort sites by name in natural order
        \App\Helper\Utilities::sortPageNaturally($sites, 'name');

        return view('livewire.show-sites', [
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
