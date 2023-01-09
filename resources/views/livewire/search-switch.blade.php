<div class="box">
    <h1 class="title is-pulled-left">Übersicht</h1>

    <div class="is-pulled-right ml-4">
        <button onclick="$('.modal-new-switch').show()" class="button is-small is-success"><i class="fa-solid fa-plus"></i></button>
    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input is-small" type="text" wire:model.debounce.500ms="searchTerm" placeholder="Search a device">
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
                <th>Firmware</th>
                <th>Standort</th>
                <th style="width:150px;text-align:center">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($devices as $device)
            @php 
                $uplinks = json_decode($device->uplinks, true);
                if($uplinks == null)
                    $uplinks = array();

                $uplinks_string = implode(',', $uplinks);
                @endphp 
            <tr>
                <td>{{ $device->name }}</td>
                <td>{{ $device->hostname }}</td>
                <td>{{ json_decode($device->system_data, true)['model'] }}</td>
                <td>{{ json_decode($device->system_data, true)['firmware'] }}</td>
                <td>{{ $locations[$device->location]->name }}, {{ $buildings[$device->building]->name }}, {{ $device->details }} #{{ $device->number }}</td>
                <td style="width:250px;">
                    <div class="has-text-centered">
                        <a class="button is-success is-small" href="/switch/{{ $device->id }}/live">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        <a class="button is-small is-link" href="{{ $https }}{{ $device->hostname }}" target="_blank">
                            <i class="fa fa-arrow-up-right-from-square"></i>
                        </a>

                        <button onclick="editSwitchModal('{{ $device->id }}', '{{ $device->name }}', '{{ $device->hostname }}', '{{ $device->location }}', '{{ $device->building }}', '{{ $device->details }}', '{{ $device->number }}', '{{ $uplinks_string }}')" class="button is-info is-small"><i class="fa fa-gear"></i></button>
                        <button onclick="deleteSwitchModal('{{ $device->id }}', '{{ $device->name }}')" class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                    </div>
                </td>
            </tr>
            @endforeach
    </table>
</div>


<div class="box">
    <div class="label is-small">Alle Switche</div>
    <div class="buttons are-small">
        <form action="post" id="form-all-devices">
            @csrf
            <a onclick="doAllDeviceAction('pubkeys', this)" class="button is-link"><i class="fa-solid fa-sync mr-2"></i> Sync Pubkeys</a>
            <a onclick="doAllDeviceAction('backups', this)" class="button is-link"><i class="fa-solid fa-hdd mr-2"></i> Create Backup</a>
            <a onclick="doAllDeviceAction('clients', this)" class="button is-link"><i class="fa-solid fa-computer mr-2"></i> Update Clients</a>
        </form>
    </div>
    <div class="is-size-7">Backups & Clients werden regelmäßig automatisiert durchgeführt</div>
</div>