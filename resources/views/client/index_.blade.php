<div class="box">
    <h1 class="title is-pulled-left">Clients (@php echo count($clients); @endphp)</h1>

    <div class="is-pulled-right ml-4">
        <a href="/printers" class="button"><i class="fa-solid fa-print mr-1"></i> / <i class="ml-1 fa-solid fa-phone mr-2"></i></a>
    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input" type="text" wire:model.debounce.500ms="searchTerm" placeholder="Search mac, ip, name, vlan">
                <span class="icon is-right">
                    <i class="fas fa-search fa-xs"></i>
                </span>
            </div>
        </div>
    </div>

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>Name</th>
                <th>IP</th>
                <th>MAC</th>
                <th>VLAN</th>
                <th>Switch</th>
                <th>Port</th>
                <th>Updated</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
            @php
                if($client->online == 1) {
                    $online = 'has-text-success';
                } elseif($client->online == 0) {
                    $online = 'has-text-danger';
                } else {
                    $online = 'has-text-link';
                }
                $chunks = str_split(strtoupper($client->mac_address), 2);
                $end = implode(':', $chunks);
            @endphp
                <tr>   
                    <td><i style="" class="fa fa-circle {{ $online }}"></i> {{ strtoupper($client->hostname) }}</td>
                    <td>{{ $client->ip_address }}</td>
                    <td>{{ $end }}</td>
                    <td>{{ $client->vlan_id }}</td>
                    <td>{{ $devices[$client->switch_id]->name }}</td>
                    <td>{{ $client->port_id }}</td>
                    <td>{{ $client->updated_at->format('d.m.Y H:i:s') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>