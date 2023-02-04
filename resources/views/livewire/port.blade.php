<tr id="{{ $port->id }}" class="pt-1 {{ $this->somethingChanged ? 'changed' : '' }}">
    <td class="has-text-centered is-vcentered">
        <i class="fas fa-circle {{ $port->link ? 'has-text-success' : 'has-text-danger' }}"></i>
    </td>

    <td class="has-text-centered is-vcentered">
        <a class="dark-fix-color" href="/switch/{{ $device_id }}/ports/{{ $port->name }}">{{ $port->name }}</a>
    </td>

    <td class="is-vcentered">
        <input {{ $this->doNotDisable ? '' : 'disabled' }} wire:change="preparePortDescription()"
            wire:model.debounce.1000ms="portDescription" class="mt-1 is-radiusless port-description-input is-link is-small input is-80"
            value="{{ $this->portDescription }}" />

        <span class="has-custom-text-warning is-size-4">{{ $this->portDescriptionUpdated ? '*' : '' }}</span>
    </td>

    <td class="is-vcentered">
        <div class="select is-small mt-1 is-link">
            <select {{ $this->doNotDisable ? '' : 'disabled' }} wire:change="prepareUntaggedVlan()" wire:model="untaggedVlanId" class="select is-radiusless port-vlan-select">
                <option value="0">No VLAN</option>
                @foreach ($vlans as $vlan)
                    <option value="{{ $vlan->id }}">{{ $vlan->name }}</option>
                @endforeach
            </select>
        </div>

        <span class="has-custom-text-warning is-size-4">{{ $this->untaggedVlanUpdated ? '*' : '' }}</span>
    </td>

    <td class="is-vcentered">
        <button class="is-80 button is-small is-outlined is-radiusless is-link mt-1"
            onclick="updateTaggedModal('{{ $port->id }}', '{{ implode(',',$port->taggedVlans()->pluck('device_vlan_id')->toArray()) }}', '{{ $port->name }}', '{{ $device_id }}', '{{ $port->vlan_mode }}')">
            {{ $port->taggedVlans()->count() ?? 0 }} VLANs
        </button>

        <span class="has-custom-text-warning is-size-4">{{ $this->taggedVlansUpdated ? '*' : '' }}</span>
    </td>

    <td class="has-text-left is-vcentered">
        @php $clients = $port->clientsList(); @endphp
        @if ($clients->count() > 0)
            <div style="width:100%" class="dropdown is-hoverable is-fullwidth">
                <div style="width:100%" class="dropdown-trigger">
                    <button class="is-small button is-radiusless is-outlined is-link is-fullwidth" aria-haspopup="true" aria-controls="dd{{ $port->id }}">
                        <span>{{ $clients->count() }} {{ trans_choice('Clients', $clients->count()) }}</span>
                        <span class="icon is-small">
                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
                <div class="dropdown-menu" id="dd{{ $port->id }}" role="menu">
                    <div class="dropdown-content">
                        <div class="dropdown-item">
                            @foreach ($clients as $client)
                                <div class="has-text-left mt-1 mb-1">
                                    <i class="{{ $cc::getClientIcon($client->type) }} mr-1"></i> {{ $client->hostname }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            <button class="button is-link is-outlined is-radiusless is-fullwidth is-small">0 {{ trans_choice('Clients', 0) }}</button>
        @endif
    </td>

    <td class="has-text-centered is-vcentered">
        <div style="height:100%;width: 100%;">
            @if ($port->speed == 0)
                <div class="tag is-link" style="width: 100%;">{{ $port->speed }}</div>
            @elseif ($port->speed == 10)
                <div class="tag is-danger"style="width: 100%;">10 Mbit/s</div>
            @elseif ($port->speed == 100)
                <div class="tag is-warning"style="width: 100%;">100 Mbit/s</div>
            @elseif ($port->speed == 1000)
                <div class="tag is-success"style="width: 100%;">1 Gbit/s</div>
            @elseif ($port->speed == 10000)
                <div class="tag" style="background-color: #3a743a;color: #fff;width: 100%;">10 Gbit/s</div>
            @endif
        </div>
    </td>
</tr>
