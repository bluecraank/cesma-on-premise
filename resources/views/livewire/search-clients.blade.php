<div class="box">
    <h1 class="title is-pulled-left">Clients</h1>

    <div class="is-pulled-right ml-4">
    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input is-small" type="text" wire:model.debounce.500ms="searchTerm" placeholder="Search clients">
                <span class="icon is-small is-right">
                    <i class="fas fa-search fa-xs"></i>
                </span>
            </div>
        </div>
    </div>

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>Hostname</th>
                <th>IP Adresse</th>
                <th>MAC Adresse</th>
                <th>VLAN</th>
                <th>Port</th>
                <th>Switch</th>
                {{-- <th style="width:150px;text-align:center">Aktionen</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
            @php
                $chunks = str_split(strtoupper($client->mac_address), 2);
                $end = implode(':', $chunks);
            @endphp
                <tr>
                    <td>{{ strtoupper($client->hostname) }}</td>
                    <td>{{ $client->ip_address }}</td>
                    <td>{{ $end }}</td>
                    <td>{{ $client->vlan_id }}</td>
                    <td>{{ $client->port_id }}</td>
                    <td>{{ $client->switch_id }}</td>
                    <td>{{ $devices[$client->switch_id]->name }}</td>

                </tr>
            @endforeach
        </tbody>
    </table>
</div>