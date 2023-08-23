<?php

namespace App\Livewire;

use App\Helper\CLog;
use App\Models\Site;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;

class SiteModals extends Component
{
    public $show = false;
    public Site $site;
    public $modal;

    #[Rule('required|min:3|max:255')]
    public $name;

    #[On('show')]
    public function show($site, $modal)
    {
        $site = Site::find($site);

        if(!$site) {
            $this->dispatch('notify-error', message: __('Site not found'));
            return;
        }

        $this->show = true;
        $this->modal = $modal;
        $this->site = $site;

        $this->name = $site->name;
    }

    public function close() {
        $this->show = false;
    }

    public function delete() {
        $this->show = false;
        if(Site::count() == 1) {
            $this->dispatch('notify-error', message: __('You cannot delete the last site'));
            return;
        }

        CLog::info("Site", __('Site :id deleted', ['id' => $this->site->id]), null, $this->site->name);

        $this->dispatch('delete', $this->site->id);
        $this->dispatch('notify-success', message: __('Site deleted'));
        $this->dispatch('refresh')->to(ShowSites::class);
    }

    public function update()
    {
        $this->validate();

        $temp = $this->site->name;
        $this->site->name = $this->name;
        $this->site->save();
        $this->show = false;
        $this->dispatch('notify-success', message: __('Site updated'));
        $this->dispatch('refresh')->to(ShowSites::class);

        CLog::info("Site", __('Site :id updated', ['id' => $this->site->id]), null, "{$temp} => {$this->name}");
    }

    public function render()
    {
        return view('modals.site.update-delete', [
            'show' => $this->show,
            'modal' => $this->modal,
            'site' => $this->site ?? null,
        ]);
    }
}
