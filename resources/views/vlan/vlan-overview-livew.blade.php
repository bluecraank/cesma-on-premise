<div class="box">
    <h1 class="title is-pulled-left">{{ __('Header.Vlans') }}</h1>

    <div class="is-pulled-right ml-4">
        @if (Auth::user()->role == 'admin')
            <button onclick="$('.modal-add-vlan').show();" class="button is-success is-small"><i
                    class="fas fa-plus mr-1"></i> {{ __('Button.Create') }}</button>
        @endif
    </div>


    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input is-small" wire:model.debounce.500ms="searchTerm" type="text"
                    placeholder="{{ __('Search.Placeh.Vlan') }}">
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
                <th>{{ __('Description') }}</th>
                <th>{{ __('Vlan.Subnet') }}</th>
                <th class="has-text-centered">Scan</th>
                <th class="has-text-centered">Sync</th>
                <th class="has-text-centered">Endgeräte-VLAN</th>

                <th style="width:150px;text-align:center">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vlans as $vlan)
                @php
                    $scan = "<i class='fas fa-times'></i>";
                    $sync = "<i class='fas fa-times'></i>";
                    $clients = "<i class='fas fa-check'></i>";
                    if ($vlan->is_scanned == 1) {
                        $scan = "<i class='fas fa-check'></i>";
                    }
                    if ($vlan->is_synced == 1) {
                        $sync = "<i class='fas fa-check'></i>";
                    }
                    if ($vlan->is_client_vlan == 0) {
                        $clients = "<i class='fas fa-times'></i>";
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
                    <td class="has-text-centered">
                        {!! $scan !!}
                    </td>
                    <td class="has-text-centered">
                        {!! $sync !!}
                    </td>
                    <td class="has-text-centered">{!! $clients !!}</td>
                    <td style="width:150px;">
                        <div class="field has-addons is-justify-content-center">
                            <div class="control">
                                <a class="button is-success is-small" href="/vlans/{{ $vlan->vid }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                            @if (Auth::user()->role == 'admin')
                                <div class="control">
                                    <button
                                        onclick="editVlanModal('{{ $vlan->id }}', '{{ $vlan->name }}', '{{ $vlan->description }}', '{{ $vlan->ip_range }}', '{{ $vlan->is_scanned }}', '{{ $vlan->is_synced }}', '{{ $vlan->is_client_vlan }}')"
                                        class="button is-info is-small"><i class="fa fa-gear"></i></button>
                                </div>
                            <div class="control">
                                <button onclick="deleteVlanModal('{{ $vlan->id }}', '{{ $vlan->name }}')"
                                    class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
