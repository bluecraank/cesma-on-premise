<tr>
    <td class="has-text-centered">
            <i class="mdi mdi-circle is-size-5 {{ $port->link ? 'has-text-success' : 'has-text-danger' }}"></i>
    </td>

    {{-- <td class="has-text-centered">{!! $port->speedAsTag() !!}</td> --}}

    <td class="has-text-centered"><b>{{ $port->name }}</b></td>

    <td>
        <input wire:change="preparePortDescription()" wire:model.debounce.1000ms="portDescription" class="input is-small" type="text" name="portDescription">
        <span class="has-custom-text-warning is-size-4">{{ $this->portDescriptionUpdated ? '*' : '' }}</span>
    </td>

    <td>
        <div class="select is-small">
            <select wire:change="prepareUntaggedVlan()" wire:model="untaggedVlanId">
                <option @empty($vlans) selected @endempty value="0">None</option>
                @foreach ($vlans as $vlan)
                    <option value="{{ $vlan->id }}" @if ($port->untagged?->id == $vlan->id) selected @endif>
                        {{ $vlan->name }}</option>
                @endforeach
            </select>
        </div>
        <span class="has-custom-text-warning is-size-4">{{ $this->untaggedVlanUpdated ? '*' : '' }}</span>
    </td>

    <td>
        <a class="no-prevent" wire:click="openTagModal">
            {{ $port->taggedVlans()->count() }} {{ trans_choice('Vlan|Vlans', $port->taggedVlans()->count()) }}
        </a>
    </td>

    <td>
        @if ($clients->count() == 0) {{ $port->clients()->count() }}
            {{ trans_choice('Client|Clients', $port->clients()->count()) }}
        @else
            <div class="dropdown is-hoverable">
                <div class="dropdown-trigger">
                    <a aria-haspopup="true" aria-controls="dropdown-menu4">
                        <span>{{ $port->clients()->count() }}
                            {{ trans_choice('Client|Clients', $port->clients()->count()) }}</span>
                        <span class="icon is-small">
                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                        </span>
                    </a>
                </div>
                <div class="dropdown-menu" id="dropdown-menu4" role="menu">
                    <div class="dropdown-content">
                        <div class="dropdown-item">
                            @foreach ($port->clients() as $client)
                                <div>{{ $client->hostname }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </td>

    <td class="has-text-centered">
        <a class="button is-small is-info" href="{{ route('show-port', [$device_id, $port->id]) }}">
            <i class="mdi mdi-eye"></i>
        </a>
    </td>
</tr>
