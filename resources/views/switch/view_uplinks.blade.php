<x-layouts.main>
    <div class="box">
        <h1 class="title is-pulled-left">{{ __('Header.Uplinks') }}</h1>

        <div class="is-pulled-right ml-4">

        </div>

        <div class="is-pulled-right">

        </div>

        <table class="table is-narrow is-hoverable is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>Switch</th>
                    <th>{{ __('Uplinks found') }}</th>
                    <th>{{ __('Custom Uplinks') }}</th>
                    <th style="width:150px;" class="has-text-centered">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $devices = $devices->sort(function ($a, $b) {
                        return strnatcmp($a['name'], $b['name']);
                    });
                @endphp
                @foreach ($devices as $device)
                    <tr>
                        <td>{{ $device['name'] }}</td>
                        @php
                            $uplinks = $device->uplinks->sort(function ($a, $b) {
                                return strnatcmp($a['name'], $b['name']);
                            })->pluck('name')->toArray();
                            $uplinks = implode(', ', $uplinks);

                            $c_uplink = $device->custom_uplink;
                            $custom_uplink = isset($c_uplink) ? implode(', ', json_decode($c_uplink->uplinks, true)) : ''; 
                        @endphp

                        <td>{{ $uplinks }}</td>
                        <td>{{ $custom_uplink }}</td>
                        <td class="has-text-centered">
                            @if (Auth::user()->role >= 1)
                                <a onclick="editUplinkModal('{{ $device->id }}', '{{ $device->name }}','{{ $custom_uplink }}')"
                                    class="button is-small is-info"><i class="fas fa-gear"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
        </table>
    </div>

    @if (Auth::user()->role >= 1)
        @include('modals.SwitchUplinkEditModal')
    @endif
    </x-layouts>
