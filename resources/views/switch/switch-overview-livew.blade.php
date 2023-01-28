<div class="box">
    <h1 class="title is-pulled-left">{{ __('Header.Switches') }}</h1>

    <div class="is-pulled-right ml-4">
        @if (Auth::user()->role == 'admin')
            <button onclick="$('.modal-new-switch').show()" class="button is-small is-success"><i
                    class="fas fa-plus"></i></button>
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

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>Name</th>
                <th>Modell</th>
                <th>Firmware</th>
                <th>Standort</th>
                <th style="width:150px;text-align:center">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($devices as $device)
                <tr>
                    <td><i title="Aktualisiert {{ $device->updated_at->diffForHumans() }}" class="mr-1 fa fa-circle {{ ($device->online) ? 'has-text-success' : 'has-text-danger' }}"></i> <a href="/switch/{{ $device->id }}">{{ $device->name }}</href></td>
                    <td>{{ $device->modelOrUnknown() }}</td>
                    <td>{{ $device->firmwareOrUnknown() }}</td>
                    <td>{{ $device->location()->first()->name ?? 'Unknown' }}, {{ $device->building()->first()->name ?? 'Unknown' }}
                        - {{ $device->room()->first()->name ?? 'Unknown' }} #{{ $device->location_number }}</td>
                    <td style="width:150px;">
                        <div class="field has-addons is-justify-content-center">
                            <div class="control">
                                <a title="{{ __('Show') }}" class="button is-success is-small" href="/switch/{{ $device->id }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                            <div class="control">
                                <a title="{{ __('GUI_External') }}" class="button is-small is-link" href="{{ $https }}{{ $device->hostname }}"
                                    target="_blank">
                                    <i class="fa fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                            @if (Auth::user()->role == 'admin')
                                <div class="control">
                                    <button title="{{ __('Switch.Edit.Hint') }}"
                                        onclick="editSwitchModal('{{ $device->id }}', '{{ $device->name }}', '{{ $device->hostname }}', '{{ $device->location_id }}', '{{ $device->building_id }}', '{{ $device->room_id }}', '{{ $device->location_number }}')"
                                        class="button is-info is-small"><i class="fa fa-gear"></i></button>
                                </div>
                                <div class="control">
                                    <button title="{{ __('Delete') }}" onclick="deleteSwitchModal('{{ $device->id }}', '{{ $device->name }}')"
                                        class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
    </table>
</div>
