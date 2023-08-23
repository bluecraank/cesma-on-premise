<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowUsers extends Component
{
    public function render()
    {
        $users = User::all();

        return view('livewire.show-users', compact('users'));
    }

    public function show($id, $type) {
        $this->dispatch('show', id: $id, type: $type)->to(UserRoleModal::class);
    }

    #[On('refresh')]
    public function refresh()
    {
        $this->render();
    }

    #[On('delete')]
    public function delete($model)
    {
        $model->delete();
    }
}
