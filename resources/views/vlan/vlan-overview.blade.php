<x-layouts.main>
    @livewire('search-vlans')

    @if (Auth::user()->role == 'admin')
    <div class="box">
        <div class="label is-small">Alle Switche</div>
        <div class="buttons are-small">
            @include('buttons.ButtonSyncVlan')
        </div>
    </div>

        @include('modals.VlanAddModal')

        @include('modals.VlanEditModal')

        @include('modals.VlanDeleteModal')

        @include('modals.VlanSyncModal')
    @endif

    </x-layouts>
