@section('title', 'All VLANs')

<x-layouts.main>
    @livewire('search-vlans')

    @if (Auth::user()->role >= 1)
    <div class="box">
        <div class="label is-small">Alle Switche</div>
        <div class="buttons are-small">
            @include('buttons.ButtonSyncVlan')
        </div>
    </div>

        @include('modals.create.VlanAddModal')

        @include('modals.edit.VlanEditModal')

        @include('modals.delete.VlanDeleteModal')

        @include('modals.VlanSyncModal')
    @endif

    </x-layouts>
