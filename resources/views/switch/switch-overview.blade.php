<x-layouts.main>
    @livewire('search-devices')

    @if (Auth::user()->role == 'admin')
        <div class="box">
            <div class="label is-small">Alle Switche</div>
            <div class="buttons are-small">
                @include('buttons.ButtonCreateBackup')
                @include('buttons.ButtonSyncPubkeys')
                @include('buttons.ButtonSyncVlan')
            </div>
        </div>

        @include('modals.create.SwitchCreateModal')

        @include('modals.edit.SwitchEditModal')

        @include('modals.delete.SwitchDeleteModal')

        @include('modals.PubkeySyncModal')

        @include('modals.VlanSyncModal')
    @endif
    </x-layouts>
