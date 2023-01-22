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

        @include('modals.SwitchCreateModal')

        @include('modals.SwitchEditModal')

        @include('modals.SwitchDeleteModal')

        @include('modals.PubkeySyncModal')

        @include('modals.VlanSyncModal')
    @endif
    </x-layouts>
