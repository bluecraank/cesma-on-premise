<div class="box">
    <h1 class="title is-pulled-left">{{ __('Header.Switches') }}</h1>

    <div class="is-pulled-right ml-4">
        @if (Auth::user()->role == 'admin')
        <button onclick="$('.modal-new-switch').show()" class="button is-small is-success"><i
                class="fa-solid fa-plus"></i></button>
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
                @php
                    $uplinks = json_decode($device->uplinks, true);
                    if ($uplinks == null) {
                        $uplinks = [];
                    }
                    
                    $uplinks_string = implode(',', $uplinks);
                @endphp
                <tr>
                    <td><i style="" class="mr-1 fa fa-circle {{ $device->online }}"></i> {{ $device->name }}</td>
                    <td>{{ json_decode($device->system_data, true)['model'] }}</td>
                    <td>{{ json_decode($device->system_data, true)['firmware'] }}</td>
                    <td>{{ $locations[$device->location]->name }}, {{ $buildings[$device->building]->name }},
                        {{ $device->details }} #{{ $device->number }}</td>
                    <td style="width:250px;">
                        <div class="has-text-centered">
                            <a class="button is-success is-small" href="/switch/{{ $device->id }}">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a class="button is-small is-link" href="{{ $https }}{{ $device->hostname }}"
                                target="_blank">
                                <i class="fa fa-arrow-up-right-from-square"></i>
                            </a>
                        @if (Auth::user()->role == 'admin')
                            <button
                                onclick="editSwitchModal('{{ $device->id }}', '{{ $device->name }}', '{{ $device->hostname }}', '{{ $device->location }}', '{{ $device->building }}', '{{ $device->details }}', '{{ $device->number }}', '{{ $uplinks_string }}')"
                                class="button is-info is-small"><i class="fa fa-gear"></i></button>
                            <button onclick="deleteSwitchModal('{{ $device->id }}', '{{ $device->name }}')"
                                class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                        @endif
                        </div>
                    </td>
                </tr>
            @endforeach
    </table>
</div>
