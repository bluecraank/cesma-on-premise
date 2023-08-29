<?php

namespace App\Livewire;


use App\Models\Device;
use App\Models\Building;
use App\Models\Notification;
use App\Models\Room;
use App\Models\Site;
use App\Services\PublicKeyService;
use App\Traits\NumberOfEntries;
use App\Traits\WithLogin;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ShowDevices extends Component
{
    use WithLogin;
    use WithPagination;
    use NumberOfEntries;

    public $searchTerm = "";
    public $numberOfEntries = 50;

    public $title = "Switches";

    public function mount()
    {
        $this->checkLogin();
    }

    public function render()
    {
        $searchTerm = '%' . $this->searchTerm . '%';
        $https = config('app.https', 'http://');

        $devices = Device::where('site_id', Auth::user()->currentSite()->id)->where(function ($query) use($searchTerm) {
            $query->where('name', 'like', $searchTerm)->orWhere('hostname', 'like', $searchTerm);
        })->orderBy('name', 'asc');

        $devices = $devices->paginate($this->numberOfEntries);

        // Sort devices by name in natural order
        $collection = $devices->sort(function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        $devices->setCollection($collection);

        $buildings = Building::where('site_id', Auth::user()->currentSite()->id)->get();

        return view('livewire.show-devices', [
            'devices' => $devices,
            'https' => $https,
            'sites' => Site::all(),
            'buildings' => $buildings,
            'rooms' => Room::whereIn('building_id', $buildings->pluck('id')->toArray())->get(),
            'keys_list' => PublicKeyService::getPubkeysDescriptionAsArray(),
        ]);
    }

    public function show($id, $modal)
    {
        $this->dispatch('show', device: $id, modal: $modal)->to(DeviceModals::class);
    }

    #[On('refresh')]
    public function refresh()
    {
        $this->render();
    }

    #[On('delete')]
    public function delete($model)
    {
        Device::where('id', $model)->delete();
    }
}
