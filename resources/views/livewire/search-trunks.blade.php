<div class="box">
    <h1 class="title is-pulled-left">Trunks</h1>

    <div class="is-pulled-right ml-4">
        
    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input is-small" type="text" wire:model.deounce.500ms="searchTerm" placeholder="Search for trunks...">
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
            </tr>
            @endforeach
    </table>
</div>