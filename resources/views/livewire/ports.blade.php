<div>
    <button wire:click="savePorts">SAVE</button>
<table id="portoverview" class="table is-striped is-narrow is-fullwidth">
    <thead>
        <tr>
            <th class="has-text-centered" style="width: 45px;">Status</th>
            <th class="has-text-centered" style="width: 60px;">Port</th>
            <th>{{ __('Switch.Live.Portname') }}</th>
            <th>Untagged/Native</th>
            <th>Tagged/Allowed</th>
            <th class="has-text-left">{{ trans_choice('Clients', 2) }}</th>
            <th class="has-text-centered" style="width: 120px;">Speed</th>
        </tr>
    </thead>

    <tbody class="live-body">
        @foreach ($ports as $port)
            <tr id="{{ $port['id'] }}" class="pt-1">
                <td>{{ $port['name'] }}</td>
                <td>
                    <input type="text" wire:model="ports.{{ $port['id'] }}.description" wire:change="updatePortDesc({{ $port['id'] }})" />
                    {{ (isset($updated[$port['id']]['descChanged'])) ? '*' : '' }}
                </td>
                <td>
                    <div class="select is-small mt-1 is-link">
                        <select wire:model="ports.{{ $port['id'] }}.untagged.id" wire:change="updatePortUntagged({{ $port['id'] }})" class="select is-radiusless port-vlan-select">
                            <option value="0">No VLAN</option>
                            @foreach ($vlans as $vlan)
                                <option value="{{ $vlan->id }}">{{ $vlan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{ (isset($updated[$port['id']]['untaggedVlanChanged'])) ? '*' : '' }}
                </td>
                <td>
                    <div class="is-small mt-1 is-link">
                        <select id="p{{ $port['id'] }}" multiple name="native-select" placeholder="Native Select" data-search="false" data-silent-initial-value-set="true" wire:model="ports.{{ $port['id'] }}.taggedId" wire:change.2000ms.debounce="updatePortTagged({{ $port['id'] }})" class="is-radiusless port-vlan-select">
                            <option value="0">No VLAN</option>
                            @foreach ($vlans as $vlan)
                                <option {{ (isset($port['taggedId'][$vlan->id])) ? 'selected' : '' }}  value="{{ $vlan->id }}">{{ $vlan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>