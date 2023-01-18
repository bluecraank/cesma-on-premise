<x-layouts.main>
    @livewire('search-vlans')

    <div class="box">
        <div class="label is-small">Alle Switche</div>
        <div class="buttons are-small">
            <form action="post" id="form-all-devices">
                @csrf
                <a onclick="$('.modal-sync-vlans').show();return false;" class="button is-info"><i
                        class="fa-solid fa-ethernet mr-2"></i> Sync VLANs</a>
            </form>
        </div>
    </div>

    @include('modals.VlanEditModal')

    @include('modals.VlanDeleteModal')

    @include('modals.VlanSyncModal')

    </x-layouts>
