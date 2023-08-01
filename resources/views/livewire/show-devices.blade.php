@section('title', __('Switches'))

<div class="box">
    <h1 class="title is-pulled-left">{{ __('Switches') }}</h1>

    <div class="is-pulled-right ml-4">
        @if (Auth::user()->role >= 1)
            <button data-modal="new-switch" class="button is-small is-success"><i
                    class="fas fa-plus mr-1"></i> {{ __('Button.Create') }}</button>
        @endif
    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input is-small" type="text" wire:model.debounce.500ms="searchTerm"
                    placeholder="{{ __('Search.Placeh.Switch') }}">
                <span class="icon is-small is-right">
                    <i class="fas fa-search fa-xs"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="is-pulled-right ml-4">
        <button title="Export zu CSV" class="button is-small is-primary export-csv-button mr-2" data-table="table" data-file-name="{{ __('Switches') }}"><i class="fa-solid fa-file-arrow-down"></i></button>
    </div>


    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>Name</th>
                <th>Modell</th>
                <th>MAC</th>
                <th>Firmware</th>
                <th>{{ __('Building') }}</th>
                <th>{{ __('Room') }}</th>
                <th>{{ __('Description') }}</th>
                <th style="width:150px;text-align:center">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @if ($devices->count() == 0)
                <tr>
                    <td colspan="7" class="has-text-centered">{{ __('Switch.NoFound') }}</td>
                </tr>
            @endif

            @foreach ($devices as $device)
                <tr>
                    @if($device->created_at == $device->updated_at)
                        <td><div title="{{ __('Hint.NewlyCreated') }}" class="mr-1 lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div> <a class="dark-fix-color">{{ $device->name }}</href></td>
                    @else
                        <td><i title="{{ __('Hint.Updated') }}{{ $device->updated_at->diffForHumans() }}" class="mr-1 fa fa-circle {{ ($device->online) ? 'has-text-success' : 'has-text-danger' }}"></i> <a class="dark-fix-color" href="{{ route('show-device', $device->id) }}">{{ $device->name }}</href></td>
                    @endif
                    <td>{{ $device->modelOrUnknown() }}</td>
                    <td>{{ $device->mac_address }}</td>
                    <td>{{ $device->firmwareOrUnknown() }}</td>
                    <td>{{ $device->building()->first()->name }}</td>
                    <td>{{ $device->room()->first()->name }}</td>
                    <td>{{ $device->location_description }}</td>
                    <td style="width:150px;">
                        <div class="field has-addons is-justify-content-center">
                            <div class="control">
                                <a title="{{ __('Show') }}" @disabled($device->created_at == $device->updated_at) @if($device->created_at != $device->updated_at) href="{{ route('show-device', $device->id) }}"  @endif class="button is-success is-small">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                            <div class="control">
                                <a title="{{ __('GUI_External') }}" class="button is-small is-link" href="{{ $https }}{{ $device->hostname }}"
                                    target="_blank">
                                    <i class="fa fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                            @if (Auth::user()->role >= 1)
                                <div class="control">
                                    <button title="{{ __('Switch.Edit.Hint') }}"
                                        data-modal="edit-switch"
                                        data-id="{{ $device->id }}"
                                        data-name="{{ $device->name }}"
                                        data-hostname="{{ $device->hostname }}"
                                        data-site_id="{{ $device->site_id }}"
                                        data-building_id="{{ $device->building_id }}"
                                        data-room_id="{{ $device->room_id }}"
                                        data-location_description="{{ $device->location_description }}"
                                        class="button is-info is-small"><i class="fa fa-gear"></i></button>
                                </div>
                                <div class="control">
                                    <button title="{{ __('Button.Delete') }}"
                                        data-modal="delete-switch"
                                        data-id="{{ $device->id }}"
                                        data-name="{{ $device->name }}"
                                        class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach

           
        </table>
        {{ $devices->links('pagination::default') }} 
</div>

@livewire('show-notifications')

@if (Auth::user()->role >= 1)
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