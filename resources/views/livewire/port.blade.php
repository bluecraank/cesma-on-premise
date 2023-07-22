<tr wire:init="fetchPort" id="{{ $port->id }}" class="pt-1 {{ $this->somethingChanged ? 'changed' : '' }}">
    @if(!$loaded)
        <td colspan="8" class="has-text-centered">
            <div class="p-3"    >
                <div style="margin: 0 auto; text-align: center;font-size:15px;" class="p-2 loader is-loading"></div>
            </div>
        </td>
    @else
        <td class="has-text-centered is-vcentered">
            <i class="fas fa-circle {{ $port->link ? 'has-text-success' : 'has-text-danger' }}"></i>
        </td>

        <td class="has-text-centered is-vcentered">
            <a class="dark-fix-color" href="/device/{{ $device_id }}/ports/{{ $port->name }}">{{ $port->name }}</a>
        </td>

        <td class="is-vcentered">
            <input {{ $this->doNotDisable ? '' : 'disabled' }}  wire:change="preparePortDescription()"
                wire:model.debounce.1000ms="portDescription" class="mt-1 is-radiusless  @if(!$readonly) port-description-input @endif is-link is-small input is-80"
                />

            <span class="has-custom-text-warning is-size-4">{{ $this->portDescriptionUpdated ? '*' : '' }}</span>
        </td>

        <td class="is-vcentered">
            <div class="select is-small mt-1 is-link">
                <select {{ $this->doNotDisable ? '' : 'disabled' }} @if(!$readonly) wire:change="prepareUntaggedVlan()" @endif wire:model="untaggedVlanId" class="select is-radiusless @if(!$readonly) port-vlan-select @endif">
                    <option value="0">No VLAN</option>
                    @foreach ($this->vlans as $vlan)
                        <option value="{{ $vlan->id }}">{{ $vlan->name }}</option>
                    @endforeach
                </select>
            </div>

            <span class="has-custom-text-warning is-size-4">{{ $this->untaggedVlanUpdated ? '*' : '' }}</span>
        </td>

        <td class="is-vcentered">
            <button class="is-80 button is-small is-outlined is-radiusless is-link mt-1"
                onclick="updateTaggedModal('{{ $port->id }}', '{{ implode(',',$this->taggedVlans->pluck('device_vlan_id')->toArray()) }}', '{{ $port->name }}', '{{ $device_id }}', '{{ $port->vlan_mode }}')">
                {{ $this->taggedVlans->count() ?? 0 }} VLANs
            </button>

            <span class="has-custom-text-warning is-size-4">{{ $this->taggedVlansUpdated ? '*' : '' }}</span>
        </td>

        <td class="has-text-left is-vcentered">
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
                                        <i class="mr-1 {{ $cc::getClientIcon($client->type) }}"></i> {{ $client->hostname ?? "DEV-".$client->mac_address }}
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
    @endif
</tr>
