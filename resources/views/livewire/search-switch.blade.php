@if ($errors->any())
<div class="notification is-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session()->has('success'))
<div class="notification is-success">
    {{ session()->get('success') }}
</div>
@endif

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
                <td>{{ $locations->find($device->location)->name }}</td>
                <td style="width:150px;">
                    <div class="has-text-centered">
                        <a href="{{ $https }}{{ $device->hostname }}" target="_blank">
                            <button class="is-small button is-primary">
                                <i class="fa fa-arrow-up-right-from-square"></i>
                            </button>
                        </a>

                        <button onclick="editSwitchModal('{{ $device->id }}', '{{ $device->name }}', '{{ $device->hostname }}', '{{ $device->location }}', '{{ $device->building }}', '{{ $device->details }}', '{{ $device->number }}' )" class="button is-info is-small"><i class="fa fa-gear"></i></button>
                        <button onclick="deleteSwitchModal('{{ $device->id }}', '{{ $device->name }}')" class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                    </div>
                </td>
            </tr>
            @endforeach
    </table>
</div>