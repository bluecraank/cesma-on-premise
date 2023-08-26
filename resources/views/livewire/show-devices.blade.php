@section('title', 'Switches')

<div>
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-switch"></i></span>
                Switches
            </p>

            <div class="mr-5 in-card-header-actions">
                <div class="is-inline-block ml-2">
                    @if (Auth::user()->role >= 1)
                        <button data-modal="create-device" wire:click="show(0, 'create')"
                            class="button is-small is-success"><i class="mdi mdi-plus mr-1"></i>
                            {{ __('Create') }}</button>
                    @endif
                </div>

                <x-export-button :filename="__('Switches')" table="table" />

                <div class="is-inline-block">
                    <div class="field">
                        <div class="control has-icons-right">
                            <input class="input is-small" type="text" wire:model.debounce.500ms="searchTerm"
                                placeholder="{{ __('Search for switches') }}">
                            <span class="icon is-small is-right">
                                <i class="mdi mdi-search-web"></i>
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        <div class="card-content">
            <div class="b-table has-pagination">
                <div class="table-wrapper has-mobile-cards">
                    <table class="table is-fullwidth is-striped is-hoverable is-narrow is-fullwidth">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Hostname</th>
                                {{-- <th>MAC</th> --}}
                                <th>Model</th>
                                <th>Firmware</th>
                                <th>{{ __('Site') }}</th>
                                <th>{{ __('Building') }}</th>
                                <th>{{ __('Room') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th class="has-text-centered">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($devices->count() == 0)
                                <tr>
                                    <td colspan="9" class="has-text-centered">
                                        <span class="icon"><i class="mdi mdi-information-outline"></i></span>
                                        {{ __('No switches found') }}
                                    </td>
                                </tr>
                            @endif
                            @foreach ($devices as $device)
                                <tr>
                                    <td><i title="{{ __('Hint.Updated') }}{{ $device->updated_at->diffForHumans() }}"
                                            class="mr-1 fa fa-circle {{ $device->active() ? 'has-text-success' : 'has-text-danger' }}"></i>
                                        <a class="dark-fix-color"
                                            href="{{ route('show-device', $device->id) }}">{{ $device->name }}
                                        </a>
                                    </td>

                                    @php
                                        $mac_chunks = str_split($device->mac_address ?? '', 2);

                                        $mac_address = strtoupper(implode(':', $mac_chunks));
                                    @endphp
                                    <td>{{ $device->hostname }}</td>
                                    {{-- <td>{{ $mac_address }}</td> --}}
                                    <td>{{ $device->model }}</td>
                                    <td>{{ $device->firmware }}</td>
                                    <td>{{ $device->site->name }}</td>
                                    <td>{{ $device->building->name }}</td>
                                    <td>{{ $device->room->name }}</td>
                                    <td>{{ $device->location_description }}</td>
                                    <td class="is-actions-cell has-text-centered">
                                        <div class="buttons is-right">
                                            <a class="button is-info is-small"
                                                href="https://{{ $device->hostname }}"><i
                                                    class="mdi mdi-open-in-new"></i></a>
                                            <a class="button is-small is-success"
                                                @if ($device->created_at != $device->updated_at) href="{{ route('show-device', $device->id) }}" @else disabled @endif><i
                                                    class="mdi mdi-eye"></i></a>
                                            <button data-modal="update-device"
                                                wire:click="show({{ $device->id }}, 'update')"
                                                class="button is-small is-primary" type="button">
                                                <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                            </button>
                                            <button data-modal="delete-device"
                                                wire:click="show({{ $device->id }}, 'delete')"
                                                class="button is-small is-danger" type="button">
                                                <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $devices->links('pagination::default') }}
            </div>
        </div>
    </div>

    @if (Auth::user()->role >= 1)
        <div>
            <section>
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <span class="icon"><i class="mdi mdi-web"></i></span>
                            {{ __('Actions for every switch') }}
                        </p>

                    </header>

                    <div class="card-content">
                        <div class="buttons are-small">
                            @include('buttons.ButtonSyncVlan')
                            @include('buttons.ButtonSyncPubkeys')
                            @include('buttons.ButtonCreateBackup')
                        </div>
                    </div>
                </div>
            </section>
        </div>
        @livewire('device-modals')
        @include('modals.PubkeySyncModal')
        {{-- @include('modals.VlanSyncModal') --}}
    @endif
</div>
