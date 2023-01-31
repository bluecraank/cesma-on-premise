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
                @foreach ($devices as $device)
                    <tr>
                        <td>{{ $device['name'] }}</td>
                        @php
                            $uplinks = $device->uplinks()->groupBy('name')->get()->pluck('name')->toArray();
                            sort($uplinks);
                            $uplinks = implode(', ', $uplinks);
                        @endphp
                        <td>{{ $uplinks }}</td>
                        <td>{{ $device->deviceCustomUplinks()->first() ? implode(',', json_decode($device->deviceCustomUplinks()->first()->uplinks, true)) : '' }}</td>
                        <td class="has-text-centered">
                            @if (Auth::user()->role >= 1)
                                <a onclick="editUplinkModal('{{ $device->id }}', '{{ $device->name }}','{{ $device->deviceCustomUplinks()->first() ? implode(',', json_decode($device->deviceCustomUplinks()->first()->uplinks, true)) : '' }}')"
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
