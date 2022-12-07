<div class="box">
    <h1 class="title is-pulled-left">Dashboard</h1>

    <div class="is-pulled-right ml-4">
        <button onclick="$('.modal-new-switch').show()" class="button is-success">Create</button>
    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input" type="text" wire:model.debounce.500ms="searchTerm" placeholder="Search a device">
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
                <th>Hostname</th>
                <th>Modell</th>
                <th>Standort</th>
                <th style="width:150px;text-align:center">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($devices as $device)
            <tr>
                <td>{{ $device->name }}</td>
                <td>{{ $device->hostname }}</td>
                <td>{{ json_decode($device->system_data, true)['product_model'] }}</td>
                <td>{{ $locations[$device->location]->name }}, {{ $buildings[$device->building]->name }}, {{ $device->details }} #{{ $device->number }}</td>
                <td style="width:250px;">
                    <div class="has-text-centered">
                        <a class="button is-blue is-small" href="/switch/live/{{ $device->id }}">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        <a class="button is-small is-primary" href="{{ $https }}{{ $device->hostname }}" target="_blank">
                            <i class="fa fa-arrow-up-right-from-square"></i>
                        </a>

                        <button onclick="editSwitchModal('{{ $device->id }}', '{{ $device->name }}', '{{ $device->hostname }}', '{{ $device->location }}', '{{ $device->building }}', '{{ $device->details }}', '{{ $device->number }}' )" class="button is-info is-small"><i class="fa fa-gear"></i></button>
                        <button onclick="deleteSwitchModal('{{ $device->id }}', '{{ $device->name }}')" class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                    </div>
                </td>
            </tr>
            @endforeach
    </table>
</div>