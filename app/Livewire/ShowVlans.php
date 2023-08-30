<?php

namespace App\Livewire;

use App\Helper\CLog;
use Livewire\Component;
use App\Models\Vlan;
use App\Traits\NumberOfEntries;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;


class ShowVlans extends Component
{
    use WithLogin;
    use WithPagination;
    use NumberOfEntries;

    #[Url]
    public $search = "";
    public $numberOfEntries = 25;

    public function mount()
    {
        $this->checkLogin();
    }

    public function render()
    {
        $search = '%' . $this->search . '%';

        $vlans = Vlan::where('site_id', Auth::user()->currentSite()->id)->where(function ($query) use ($search) {
            $query->where('name', 'like', $search)
            ->orWhere('vid', 'like', $search)
            ->orWhere('description', 'like', $search);
        })->orderBy('vid')->paginate($this->numberOfEntries ?? 25);

        $vlans->sort(function ($a, $b) {
            return strnatcmp($a->vid, $b->vid);
        });


        return view('livewire.show-vlans', [
            'vlans' => $vlans,
        ]);
    }

    public function show($id, $modal)
    {
        $this->dispatch('show', vlan: $id, modal: $modal)->to(VlanModals::class);
    }

    #[On('refresh')]
    public function refresh()
    {
        $this->render();
    }

    public function updateSlider($vlan_id, $type, $initialState = false) {
        $vlan = Vlan::where('id', $vlan_id)->first();

        if(!$vlan) {
            $this->dispatch('notify-error', message: __('Vlan not found'));
            return;
        }

        if($type == "clients") {
            $vlan->is_client_vlan = !$initialState;
        } elseif($type == "sync") {
            $vlan->is_synced = !$initialState;
        }

        if($vlan->save()) {
            $this->dispatch('notify-success', message: __('Vlan updated'));
            CLog::info("Vlan", __('Vlan :name updated', ['name' => $vlan->name]), null, $type . " => " . json_encode(!$initialState));
        } else {
            $this->dispatch('notify-error', message: __('Vlan could not be updated'));
            CLog::error("Vlan", __('Vlan :name could not be updated', ['name' => $vlan->name]), null, $type . " => " . json_encode(!$initialState));
        }

    }

    #[On('delete')]
    public function delete($model)
    {
        Vlan::where('id', $model)->delete();
    }
}
