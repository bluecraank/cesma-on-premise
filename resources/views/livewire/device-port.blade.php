<tr id="{{ $id }}">
    <td class="has-text-centered">
        <i class="mdi mdi-circle is-size-5 {{ $link ? 'has-text-success' : 'has-text-danger' }}"
            wire:loading.class="is-hidden"></i>
        <button class="is-white is-loading button" style="display:none" wire:loading.delay></button>
    </td>

    <td class="has-text-centered">{!! $speed !!}</td>

    <td class="has-text-centered"><b>{{ $name }}</b></td>

    <td>
        <input x-bind:disabled="!editable" wire:change="updateProperty('description')"
            wire:model.live.debounce.1000ms="description" class="input is-small" type="text" name="portDescription">
        <span
            class="has-custom-text-warning is-size-4">{{ isset($this->propertyUpdated['description']) ? '*' : '' }}</span>
    </td>

    <td>
        <div class="select is-small">
            <select x-bind:disabled="!editable" wire:change="updateProperty('untagged')" wire:model.live="untagged">
                <option @empty($vlans) selected @endempty value="0">None</option>
                @foreach ($vlans as $vlan)
                    <option value="{{ $vlan->id }}" @if ($untagged == $vlan->id) selected @endif>
                        {{ $vlan->name }}</option>
                @endforeach
            </select>
        </div>
        <span
            class="has-custom-text-warning is-size-4">{{ isset($this->propertyUpdated['untagged']) ? '*' : '' }}</span>
    </td>

    <td>
        @if($vlan_mode == "native-untagged")
        <a class="no-prevent" wire:click="openTagModal">
            {{ count($tagged) }} {{ trans_choice('Vlan|Vlans', count($tagged)) }}
        </a>
        <span class="has-custom-text-warning is-size-4">{{ isset($this->propertyUpdated['tagged']) ? '*' : '' }}</span>
        @else
        Access mode
        @endif
    </td>

    <td>
        @if ($clients == 0)
            {{ $clients }} {{ trans_choice('Client|Clients', $clients) }}
        @else
            <div class="dropdown is-hoverable">
                <div class="dropdown-trigger">
                    <a aria-haspopup="true" aria-controls="dropdown-menu4">
                        <span>{{ $clients }}
                            {{ trans_choice('Client|Clients', $clients) }}</span>
                        <span class="icon is-small">
                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                        </span>
                    </a>
                </div>
                <div class="dropdown-menu" id="dropdown-menu4" role="menu">
                    <div class="dropdown-content">
                        <div class="dropdown-item">
                            @foreach ($port_clients as $client)
                                 @php $hostname = strstr($client['hostname'], ".", true); @endphp
                                <div width="100%"><i class="mdi {{ $client['type_icon'] }}"></i> {{ $hostname != "" ? $hostname : $client['hostname'] ?? "DEV-".$client['mac_address'] }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </td>
</tr>
