<?php

namespace App\Livewire;

use App\Helper\CLog;
use App\Models\Permission;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class UserRoleModal extends Component
{
    public $showModal = false;
    public $type;
    public $user;

    public $name;
    public $guid;
    public $role;

    public $permissions = [];

    public function render()
    {
        $sites = Site::all();
        return view('livewire.user-role-modal', compact('sites'));
    }

    #[On('show')]
    public function show($id, $type)
    {
        $this->user = User::find($id);

        $this->type = $type;

        if(!$this->user) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'User not found'
            ]);
            return;
        }

        $this->name = $this->user->name;
        $this->guid = $this->user->guid;
        $this->role = $this->user->role;

        $this->showModal = true;
    }

    public function save() {
        if(Auth::user()->guid == $this->user->guid) {
            $this->dispatch('notify-error', message: __('You cannot update yourself'));
            return;
        }

        $this->user->name = $this->name;
        $this->user->guid = $this->guid;
        $this->user->role = $this->role;
        $this->user->save();

        foreach($this->permissions as $permission) {
            Permission::updateOrCreate([
                'guid' => $this->user->guid,
                'site_id' => $permission,
                'role' => $this->role
            ]);
        }

        $this->showModal = false;
        $this->dispatch('notify-success', message: __('User :user updated', ['user' => $this->user->name]));
        $this->dispatch('refresh')->to(ShowUsers::class);
        CLog::info("User", __('User :id updated', ['id' => $this->user->id]), null, $this->user->name);
        // $this->reset();

        $this->showModal = false;
    }

    public function delete() {
        if(Auth::user()->guid == $this->user->guid) {
            $this->dispatch('notify-error', message: __('You cannot delete yourself'));
            return;
        }

        Permission::where('guid', $this->user->guid)->delete();
        $user = $this->user;

        $this->showModal = false;
        $this->dispatch('notify-success', message: __('User :user deleted', ['user' => $this->user->name]));
        $this->dispatch('refresh')->to(ShowUsers::class);
        CLog::info("User", __('User :id deleted', ['id' => $this->user->id]), null, $this->user->name);
        $user->delete();
        $this->user = null;
        $this->name = null;
    }

    public function close() {
        $this->showModal = false;
    }
}
