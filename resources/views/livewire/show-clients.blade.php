@inject('cc', 'App\Services\ClientService')
@section('title', __('Clients'))

<div class="card has-table">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-desktop-classic"></i></span>
            {{ __('Clients') }}
        </p>

        <div class="mr-5 in-card-header-actions">
            <x-export-button :filename="__('Switches')" table="table" />
        </div>
    </header>

    <div class="card-content">
        <div class="b-table has-pagination">
            <div class="table-wrapper has-mobile-cards">
                <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>
                                <div class="field">
                                    <label data-row="0" class="label is-small">NAME <i
                                            class="fa-angle-up ml-1 fas"></i></label>
                                    <div class="control is-small">
                                        <input wire:model.live.debounce.500ms="cHOSTNAME"
                                            class="input is-small is-radiusless" type="text">
                                    </div>
                                </div>
                            </th>
                            <th>
                                <div class="field ">
                                    <label data-row="1" class="label is-small">IP <i
                                            class="is-hidden ml-1 fas fa-angle-up"></i></label>
                                    <div class="control is-small">
                                        <input wire:model.live.debounce.500ms="cIP" class="input is-small is-radiusless"
                                            type="text">
                                    </div>
                                </div>
                            </th>
                            <th>
                                <div class="field ">
                                    <label data-row="2" class="label is-small">MAC <i
                                            class="is-hidden ml-1 fas fa-angle-up"></i>
                                        <i title="Hover mac address to see vendor"
                                            class="ml-2 fas fa-circle-info"></i></label>
                                    <div class="control is-small">
                                        <input wire:model.live.debounce.500ms="cMAC" class="input is-small is-radiusless"
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
                                            <select wire:model.live.debounce.500ms="cVLAN" class="is-radiusless">
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
                                            <select wire:model.live.debounce.500ms="cSWITCH" class="is-radiusless">
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
                                        <input wire:model.live.debounce.500ms="cPORT" class="input is-small is-radiusless"
                                            type="text">
                                    </div>
                                </div>
                            </th>
                            <th>
                                <div class="field is-inline-block-desktop">
                                    <label data-row="0" class="label is-small">TYPE</label>
                                    <div class="control is-small">
                                        <div class="select is-small is-radiusless">
                                            <select style="width:100px;" wire:model.live.debounce.500ms="cTYPE"
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
                                <td colspan="7" class="has-text-centered is-size-4">
                                    <b>{{ __('No client data found') }}</b></td>
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
                                <td style="width:300px" class="hostname-cell"
                                    title="Online N/A, Last update {{ trim($client->updated_at->format('d.m.Y H:i:s')) }}">
                                    <i style=""
                                        class="client-status mr-2 {{ $cc::getClientIcon($client->type) }}"></i><span
                                        title="{{ $client->hostname ?? 'DEV-' . $client->mac_address }}"
                                        class="client-hostname">{{ trim($client->hostname ?? 'DEV-' . $client->mac_address) }}</span>
                                </td>

                                <td>{{ $client->ip_address }}</td>
                                <td title="{{ $vendor }}">{{ trim($formatted_mac) }}</td>
                                <td><a class="dark-fix-color"
                                        href="{{ route('show-vlan', $vlans[$client->vlan_id]->id) }}">{{ trim($client->vlan_id) }}</a>
                                </td>
                                <td><a class="dark-fix-color"
                                        href="{{ route('show-device', $client->device_id) }}">{{ trim($devices[$client->device_id]->name) }}</a>
                                </td>
                                <td style="width:100px"><a class="dark-fix-color"
                                        href="{{ route('show-port', [$client->device_id, $client->port_id]) }}">{{ trim($client->port_id) }}</a>
                                </td>
                                <td class="has-text-centered">{{ trim($client->updated_at->format('d.m.Y H:i:s')) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{ $clients->links('pagination::default') }}
</div>
