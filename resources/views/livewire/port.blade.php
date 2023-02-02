<tr id="{{ $port->id }}" class="{{ ($this->somethingChanged) ? 'changed' : '' }}">
    <td class="has-text-centered"s><i
            class="fas fa-circle {{ $port->link ? 'has-text-success' : 'has-text-danger' }}"></i></td>
    <td class="has-text-centered">{{ $port->name }}</td>
    <td>{{ $port->description }}</td>
    <td>
        <div class="select {{ $this->untaggedVlansUpdated ? 'has-text-warning-dark' : '' }} is-small">
            <select wire:change="prepareUntaggedVlan()" wire:model="untaggedVlanId" class="select port-vlan-select"
                {{ $doNotDisable ? '' : 'disabled' }} name="" id="">
                <option value="0">{{ $this->port->untaggedVlan() }}</option>
                @foreach ($vlans as $vlan)
                    <option {{ $this->port->untaggedVlan() == $vlan->id ? 'selected' : '' }} value="{{ $vlan->id }}">
                        {{ $vlan->name }}</option>
                @endforeach
            </select>
        </div>
        {!! $this->untaggedVlansUpdated ? '<i class="ml-1 is-warning is-size-7 fa-solid fa-asterisk"></i>' : '' !!}
    </td>
    <td class="has-text-right">
        {!! $this->taggedVlansUpdated ? '<i class="ml-1 is-warning is-size-7 fa-solid fa-asterisk"></i>' : '' !!}
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
