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
    use \App\Traits\ChecksRole;
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
        $this->requireSuperAdmin();
        $this->user = User::find($id);

        $this->type = $type;

        if(!$this->user) {
            $this->dispatch('notify-error', message: __('User not found'));
            return;
        }

        $this->name = $this->user->name;
        $this->guid = $this->user->guid;
        $this->role = $this->user->role;
        $this->permissions = Permission::where('guid', $this->user->guid)->pluck('site_id')->map(fn ($id) => (string) $id)->toArray();

        $this->showModal = true;
    }

    public function save() {
        $this->requireSuperAdmin();
        if(Auth::user()->guid == $this->user->guid) {
            $this->dispatch('notify-error', message: __('You cannot update yourself'));
            return;
        }

        $this->validate([
            'role' => 'required|integer|in:0,1,2',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:sites,id',
        ]);

        // Name and guid come from LDAP and must not be changed by the client
        $oldRole = $this->user->role;
        $oldSites = Permission::where('guid', $this->user->guid)->pluck('site_id')->sort()->values()->toArray();
        $this->user->role = (int) $this->role;
        $this->user->save();

        // Replace the site permissions so unchecked sites are removed
        Permission::where('guid', $this->user->guid)->delete();
        foreach(array_unique($this->permissions) as $site_id) {
            Permission::create([
                'guid' => $this->user->guid,
                'site_id' => $site_id,
                'role' => $this->user->role,
            ]);
        }

        $this->showModal = false;
        $this->dispatch('notify-success', message: __('User :user updated', ['user' => $this->user->name]));
        $this->dispatch('refresh')->to(ShowUsers::class);
        CLog::info("User", __('User :name updated', ['name' => $this->user->name]), null, [
            'guid' => $this->user->guid,
            'role' => ['old' => $oldRole, 'new' => $this->user->role],
            'sites' => ['old' => $oldSites, 'new' => collect($this->permissions)->map(fn ($id) => (int) $id)->unique()->sort()->values()->toArray()],
        ]);
        // $this->reset();

        $this->showModal = false;
    }

    public function delete() {
        $this->requireSuperAdmin();
        if(Auth::user()->guid == $this->user->guid) {
            $this->dispatch('notify-error', message: __('You cannot delete yourself'));
            return;
        }

        Permission::where('guid', $this->user->guid)->delete();
        $user = $this->user;

        $this->showModal = false;
        $this->dispatch('notify-success', message: __('User :user deleted', ['user' => $this->user->name]));
        $this->dispatch('refresh')->to(ShowUsers::class);
        CLog::info("User", __('User :name deleted', ['name' => $this->user->name]), null, ['guid' => $this->user->guid, 'role' => $this->user->role]);
        $user->delete();
        $this->user = null;
        $this->name = null;
    }

    public function close() {
        $this->showModal = false;
    }
}
