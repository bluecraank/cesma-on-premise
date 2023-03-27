@inject('cc', 'App\Services\ClientService')

<div class="box">
    <h1 class="title is-pulled-left">{{ trans_choice('Clients', 2) }}</h1>

    <div class="is-pulled-right ml-4">
    </div>

    <div class="is-pulled-right">
    </div>

    <div class="is-clearfix"></div>


    {{-- <div class="notification is-danger">
        <p>
            <b>Learning-Phase:</b> Die Client-Übersicht generiert sich über Zeit. Es kann daher sein, dass nicht
            korrekte Daten angezeigt werden.
        </p>
    </div> --}}

    <div class="table-container">
        <table class="table is-narrow is-hoverable is-striped is-fullwidth ">
            <thead>
                <tr>
                    <th>
                        <div class="field">
                            <label data-row="0" class="label is-small">NAME <i
                                    class="fa-angle-up ml-1 fas"></i></label>
                            <div class="control is-small">
                                <input wire:model.debounce.500ms="cHOSTNAME" class="input is-small is-radiusless"
                                    type="text">
                            </div>
                        </div>
                    </th>
                    <th>
                        <div class="field ">
                            <label data-row="1" class="label is-small">IP <i
                                    class="is-hidden ml-1 fas fa-angle-up"></i></label>
                            <div class="control is-small">
                                <input wire:model.debounce.500ms="cIP" class="input is-small is-radiusless"
                                    type="text">
                            </div>
                        </div>
                    </th>
                    <th>
                        <div class="field ">
                            <label data-row="2" class="label is-small">MAC <i
                                    class="is-hidden ml-1 fas fa-angle-up"></i>
                                <i title="Hover mac address to see vendor" class="ml-2 fas fa-circle-info"></i></label>
                            <div class="control is-small">
                                <input wire:model.debounce.500ms="cMAC" class="input is-small is-radiusless"
                                    type="text">
                            </div>
                        </div>
                    </th>
                    <th>
                        <div class="field ">
                            <label data-row="3" class="label is-small">VLAN <i
                                    class="is-hidden ml-1 fas fa-angle-up"></i></label>
                            <div class="control is-small">
                                <div class="select is-small is-fullwidth">
                                    <select wire:model.debounce.500ms="cVLAN" class="is-radiusless">
                                        <option value="all">ALL</option>
                                        @foreach ($vlans as $vlan)
                                            <option value="{{ $vlan->vid }}">{{ $vlan->vid }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </th>
                    <th>
                        <div class="field ">
                            <label data-row="4" class="label is-small">SWITCH <i
                                    class="is-hidden ml-1 fas fa-angle-up"></i></label>
                            <div class="control is-small">
                                <div class="select is-small is-radiusless is-fullwidth">
                                    <select wire:model.debounce.500ms="cSWITCH" class="is-radiusless">
                                        <option value="all">ALL</option>
                                        @foreach ($devices as $device)
                                            <option value="{{ $device->id }}">{{ $device->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </th>
                    <th>
                        <div class="field">
                            <label data-row="5" class="label is-small">PORT <i
                                    class="is-hidden ml-1 fas fa-angle-up"></i></label>
                            <div class="control is-small">
                                <input wire:model.debounce.500ms="cPORT" class="input is-small is-radiusless"
                                    type="text">
                            </div>
                        </div>
                    </th>
                    <th>
                        <div class="field is-inline-block-desktop">
                            <label data-row="0" class="label is-small">TYPE</label>
                            <div class="control is-small">
                                <div class="select is-small is-radiusless">
                                    <select style="width:100px;" wire:model.debounce.500ms="cTYPE"
                                        class="is-radiusless">
                                        <option value="all">ALL</option>
                                        <option value="client">Client</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </th>
            </thead>
            <tbody>
                @if ($clients->count() == 0)
                    <tr>
                        <td colspan="7" class="has-text-centered">{{ __('Clients.NoFound') }}</td>
                    </tr>
                @endif

                @foreach ($clients as $client)
                    @php
                        $vendor = substr($client->mac_address, 0, 6);
                        $ven_found = isset($vendors[$vendor]);
                        if ($ven_found) {
                            $vendor = $vendors[$vendor]->vendor_name;
                        } else {
                            $vendor = 'Unknown';
                        }
                        
                        $splitted_mac = str_split(strtoupper($client->mac_address), 2);
                        $formatted_mac = implode(':', $splitted_mac);
                    @endphp
                    <tr class="client-table-row">
                        <td style="width:300px" class="hostname-cell" title="Online N/A, Last update {{ trim($client->updated_at->format('d.m.Y H:i:s')) }}"><i style=""
                                class="client-status mr-2 {{ $cc::getClientIcon($client->type) }}"></i><span
                                title="{{ $client->hostname ?? 'DEV-' . $client->mac_address }}"
                                class="client-hostname">{{ trim($client->hostname ?? 'DEV-' . $client->mac_address) }}</span>
                        </td>

                        <td>{{ $client->ip_address }}</td>
                        <td title="{{ $vendor }}">{{ trim($formatted_mac) }}</td>
                        <td><a class="dark-fix-color"
                                href="/vlans/{{ $client->vlan_id }}">{{ trim($client->vlan_id) }}</a></td>
                        <td><a class="dark-fix-color"
                                href="/switch/{{ $client->device_id }}">{{ trim($devices[$client->device_id]->name) }}</a>
                        </td>
                        <td style="width:100px"><a class="dark-fix-color"
                                href="/switch/{{ $client->device_id }}/ports/{{ $client->port_id }}">{{ trim($client->port_id) }}</a>
                        </td>
                        <td class="has-text-centered">{{ trim($client->updated_at->format('d.m.Y H:i:s')) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div>
        {{ $clients->links('pagination::default') }}
    </div>
</div>
