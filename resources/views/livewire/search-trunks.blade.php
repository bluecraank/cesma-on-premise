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
    <h1 class="title is-pulled-left">Trunks</h1>

    <div class="is-pulled-right ml-4">
        <button class="button is-success">Create</button>
    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input" type="text" wire:model.deounce.500ms="searchTerm" placeholder="Search for trunks...">
                <span class="icon is-small is-right">
                    <i class="fas fa-search fa-xs"></i>
                </span>
            </div>
        </div>
    </div>

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>Switch</th>
                <th>Trunks</th>
                <th style="width:150px;text-align:center">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($devices as $device)
            @php
            $trunks = array();
            foreach(json_decode($device->port_data, true)['port_element'] as $trunk)
            {
                if(str_contains($trunk['id'], 'Trk'))
                {
                    array_push($trunks, $trunk['id']);
                }
            }
            @endphp
            <tr>
                <td>{{ $device->name }}</td>
                <td>{{ implode(', ', $trunks) }}</td>
                <td style="width:150px;">
                    <div class="has-text-centered">
                        <button onclick='refreshTrunksModal({{ $device->id }})' class="button is-info is-small"><i class="fa-solid fa-arrows-rotate"></i></button>
                    </div>
                </td>
            </tr>
            @endforeach
    </table>
</div>