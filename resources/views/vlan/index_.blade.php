<div class="box">
    <h1 class="title is-pulled-left">VLANs</h1>

    <div class="is-pulled-right ml-4">
        <button class="button is-success is-small"><i class="fa-solid fa-plus"></i></button>
    </div>


    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input is-small" wire:model.debounce.500ms="searchTerm" type="text" placeholder="Search a vlan...">
                <span class="icon is-small is-right">
                    <i class="fas fa-search fa-xs"></i>
                </span>
            </div>
        </div>
    </div>

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Beschreibung</th>
                <th>IP-Bereich</th>
                <th>Scan</th>
                <th style="width:150px;text-align:center">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vlans as $vlan)
            @php
                $scan = "Nein";
                if($vlan->scan == 1) {
                    $scan = "Ja";
                }
            @endphp
            <tr>
                <td>
                    {{ $vlan->vid }}
                </td>
                <td>
                    {{ $vlan->name }}
                </td>
                <td>
                    {{ $vlan->description }}
                </td>

                <td>
                    {{ $vlan->ip_range }}
                </td>
                <td>
                    {{ $scan }}
                </td>

                <td style="width:150px;">
                    <div class="has-text-centered">
                    <a class="button is-success is-small" href="/vlans/{{ $vlan->vid }}">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <button onclick="editVlanModal('{{ $vlan->id }}', '{{ $vlan->name }}', '{{ $vlan->description }}', '{{ $vlan->ip_range }}', '{{ $vlan->scan }}')" class="button is-info is-small"><i class="fa fa-gear"></i></button>
                        <button onclick="deleteVlanModal('{{ $vlan->id }}', '{{ $vlan->name }}')" class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>