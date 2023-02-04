<tr id="{{ $port->id }}" class="{{ $this->somethingChanged ? 'changed' : '' }}">
    <td class="has-text-centered"s><i
            class="fas fa-circle {{ $port->link ? 'has-text-success' : 'has-text-danger' }}"></i></td>
    <td class="has-text-centered">{{ $port->name }}</td>
    <td>
        <input {{ ($this->doNotDisable) ? '' : 'readonly="true"' }} wire:change="preparePortDescription()" wire:model.debounce.1000ms="portDescription" class="port-description-input is-small input" value="{{ $this->portDescription }}" />
        <i class="{{ $this->portDescriptionUpdated ? '' : 'is-hidden' }} ml-1 is-warning is-size-7 fa-solid fa-asterisk"></i>
    </td>
    <td>
        <div class="select is-small">
            <select wire:change="prepareUntaggedVlan()" wire:model="untaggedVlanId" class="select port-vlan-select"
                {{ ($this->doNotDisable) ? '' : 'disabled' }} name="" id="">
                <option value="0">No VLAN</option>
                @foreach ($vlans as $vlan)
                    <option value="{{ $vlan->id }}">{{ $vlan->name }}</option>
                @endforeach
                {{-- {{ $this->untaggedVlanId == $vlan->id ? 'selected' : '' }} --}}
            </select>
        </div>

        <i class="{{ $this->untaggedVlanUpdated ? '' : 'is-hidden' }} ml-1 is-warning is-size-7 fa-solid fa-asterisk"></i>
    </td>
    <td class="has-text-right">
        <i class="{{ $this->taggedVlansUpdated ? '' : 'is-hidden' }} ml-1 is-warning is-size-7 fa-solid fa-asterisk"></i>
        <a class="dark-fix-color"
            onclick="updateTaggedModal('{{ $port->id }}', '{{ implode(',',$port->taggedVlans()->pluck('device_vlan_id')->toArray()) }}', '{{ $port->name }}', '{{ $device_id }}', '{{ $port->vlan_mode }}')">
            {{ $port->taggedVlans()->count() ?? 0 }} VLANs
        </a>
    </td>
    <td class="has-text-centered">{{ $port->clientsList()->count() }}</td>
    <td class="has-text-centered">
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
