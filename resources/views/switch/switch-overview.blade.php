<x-layouts.main>
    @livewire('search-devices')
    
    @if (Auth::user()->role == 'admin')
    <div class="box">
        <div class="label is-small">Alle Switche</div>
        <div class="buttons are-small">
            <form action="post" id="form-all-devices">
                <a onclick="device_overview_actions('backups', this)" class="button is-info"><i
                        class="fa-solid fa-hdd mr-2"></i> {{ __('Button.CreateBackup') }}</a>
                <a onclick="$('.modal-sync-vlans').show();return false;" class="button is-info"><i
                        class="fa-solid fa-ethernet mr-2"></i> {{ __('Button.SyncVlans') }}</a>
                <a onclick="device_overview_actions('pubkeys', this)" class="sync-pubkeys-button button is-info"><i
                        class="fa-solid fa-sync mr-2"></i> {{ __('Button.SyncPubkeys') }}</a>
            </form>
        </div>
    </div>

    @include('modals.SwitchCreateModal')

    @include('modals.SwitchEditModal')

    @include('modals.SwitchDeleteModal')

    @include('modals.PubkeySyncModal')

    @include('modals.VlanSyncModal')

    @endif
    </x-layouts>
