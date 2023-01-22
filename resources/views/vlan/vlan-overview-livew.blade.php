<div class="box">
    <h1 class="title is-pulled-left">{{ __('Header.Vlans') }}</h1>

    <div class="is-pulled-right ml-4">
        @if (Auth::user()->role == 'admin')
            <button onclick="$('.modal-add-vlan').show();" class="button is-success is-small"><i
                    class="fa-solid fa-plus"></i></button>
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
                <th style="width:150px;text-align:center">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vlans as $vlan)
                @php
                    $scan = "<i class='fa-solid fa-times'></i>";
                    $sync = "<i class='fa-solid fa-times'></i>";
                    if ($vlan->scan == 1) {
                        $scan = "<i class='fa-solid fa-check'></i>";
                    }
                    if ($vlan->sync == 1) {
                        $sync = "<i class='fa-solid fa-check'></i>";
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
                    <td style="width:150px;">
                        <div class="has-text-centered">
                            <a class="button is-success is-small" href="/vlans/{{ $vlan->vid }}">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            @if (Auth::user()->role == 'admin')
                                <button
                                    onclick="editVlanModal('{{ $vlan->id }}', '{{ $vlan->name }}', '{{ $vlan->description }}', '{{ $vlan->ip_range }}', '{{ $vlan->scan }}', '{{ $vlan->sync }}')"
                                    class="button is-info is-small"><i class="fa fa-gear"></i></button>
                                <button onclick="deleteVlanModal('{{ $vlan->id }}', '{{ $vlan->name }}')"
                                    class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
