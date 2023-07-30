@section('title', 'All VLANs')

<div class="box">
    <h1 class="title is-pulled-left">{{ __('Vlans') }}</h1>

    <div class="is-pulled-right ml-4">
        @if (Auth::user()->role >= 1)
            <button data-modal="add-vlan" class="button is-success is-small"><i class="fas fa-plus mr-1"></i>
                {{ __('Button.Create') }}</button>
        @endif
    </div>


    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input is-small" wire:model.debounce.500ms="searchTerm" type="text"
                    placeholder="{{ __('Search.Placeh.Vlan') }}">
                <span class="icon is-small is-right">
                    <i class="fas fa-search fa-xs"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="is-pulled-right">
        <button title="Export zu CSV" class="button is-small is-primary export-csv-button mr-2" data-table="table" data-file-name="{{ __('Vlans') }}"><i class="fa-solid fa-file-arrow-down"></i></button>
    </div>

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Vlan.Subnet') }}</th>
                <th class="has-text-centered">Sync</th>
                <th class="has-text-centered">Endgeräte</th>

                <th style="width:150px;text-align:center">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @if ($vlans->count() == 0)
                <tr>
                    <td colspan="8" class="has-text-centered">{{ __('Vlan.NoFound') }}</td>
                </tr>
            @endif

            @foreach ($vlans as $vlan)
                @php
                    $sync = $clients = false;
                    if ($vlan->is_synced == 1) {
                        $sync = true;
                    }
                    if ($vlan->is_client_vlan == 1) {
                        $clients = true;
                    }
                @endphp
                <tr>
                    <td>
                        {{ $vlan->vid }}
                    </td>
                    <td>
                        {{ $vlan->name }}
                    </td>
                    <td>
                        {{ $vlan->description }}
                    </td>

                    <td>
                        {{ $vlan->ip_range }}
                    </td>
                    <td class="has-text-centered">
                        <i class='fas {{ $sync ? 'fa-check' : 'fa-times' }}'></i>
                        <span class="is-hidden">{{ $sync ? 'Ja' : 'Nein' }}</span>
                    </td>
                    <td class="has-text-centered">
                        <i class='fas {{ $clients ? 'fa-check' : 'fa-times' }}'></i>
                        <span class="is-hidden">{{ $clients ? 'Ja' : 'Nein' }}</span>
                    </td>
                    <td style="width:150px;">
                        <div class="field has-addons is-justify-content-center">
                            <div class="control">
                                <a class="button is-success is-small" href="/vlans/{{ $vlan->id }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                            @if (Auth::user()->role >= 1)
                                <div class="control">
                                    <button data-modal="edit-vlan" data-id="{{ $vlan->vid }}"
                                        data-name="{{ $vlan->name }}" data-description="{{ $vlan->description }}"
                                        data-ip_range="{{ $vlan->ip_range }}"
                                        data-is_scanned="{{ $vlan->is_scanned }}"
                                        data-is_synced="{{ $vlan->is_synced }}"
                                        data-is_client_vlan="{{ $vlan->is_client_vlan ?? '0' }}"
                                        class="button is-info is-small"><i class="fa fa-gear"></i></button>
                                </div>
                                <div class="control">
                                    <button data-modal="delete-vlan" data-id="{{ $vlan->vid }}"
                                        data-name="{{ $vlan->name }}" class="button is-danger is-small"><i
                                            class="fa fa-trash-can"></i></button>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $vlans->links('pagination::default') }}
</div>

@if (Auth::user()->role >= 1)
    <div class="box">
        <div class="label is-small">Alle Switche</div>
        <div class="buttons are-small">
            @include('buttons.ButtonSyncVlan')
        </div>
    </div>

    @include('modals.create.CreateVlanModal')

    @include('modals.edit.VlanEditModal')

    @include('modals.delete.DeleteVlanModal')

    @include('modals.VlanSyncModal')
@endif
