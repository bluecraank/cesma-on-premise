<x-layouts.main>
<div class="box">
    <h1 class="title is-pulled-left">Drucker & Telefone (@php echo count($printers); @endphp)</h1>

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
                <th>Type</th>
                <th>Updated</th>
            </tr>
        </thead>
        <tbody>
            @foreach($printers as $printer)
            @php
                $chunks = str_split(strtoupper($printer->mac_address), 2);
                $end = implode(':', $chunks);
            @endphp
                <tr>   
                    <td>{{ strtoupper($printer->hostname) }}</td>
                    <td>{{ $printer->ip_address }}</td>
                    <td>{{ $end }}</td>
                    <td>{{ $printer->vlan_id }}</td>
                    <td>{{ $devices[$printer->device_id]->name }}</td>
                    <td>{{ $printer->port_id }}</td>
                    <td>{{ $printer->type }}</td>
                    <td>{{ $printer->updated_at->format('d.m.Y H:i:s') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

</x-layouts>
