<div class="box">
    <h1 class="title is-pulled-left">Geräte (@php echo count($clients); @endphp)</h1>

    <div class="is-pulled-right ml-4">
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

                if($client->type == "client") $type = 'computer';
                if($client->type == "printer") $type = 'print';
                if($client->type == "phone") $type = 'phone';

                $chunks = str_split(strtoupper($client->mac_address), 2);
                $end = implode(':', $chunks);
            @endphp
                <tr>   
                    <td><i style="" class="mr-2 fa fa-{{ $type }} {{ $online }}"></i> {{ substr(strtoupper($client->hostname),0,20) }}</td>
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
    @php $elements = json_decode($clients->toJson(),true)['links']; @endphp
    @if ($clients->hasPages())
    <nav class="pagination" role="navigation" aria-label="pagination">
        <a class="pagination-previous" href="{{ $clients->previousPageUrl() }}">Vorherige Seite</a>
        <a class="pagination-next" href="{{ $clients->nextPageUrl() }}">Nächste Seite</a>
        <ul class="pagination-list">
            @foreach ($elements as $key => $element)
                @if ($key != 0 and $key != count($elements)-1)
                <li>
                    <a href="{{ $clients->url($key) }}" class="pagination-link {{ ($key == $clients->currentPage()) ? 'is-current' : '' }}" aria-label="Page 1" aria-current="page">{{ $key }}</a>
                </li>
                @endif
            @endforeach
        </ul>
      </nav>
    @endif