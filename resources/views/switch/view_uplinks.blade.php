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
                    <th>Uplinks</th>
                    <th style="width:150px;" class="has-text-centered">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($devices as $device)
                    <tr>
                        <td>{{ $device['name'] }}</td>
                        @php
                            $uplinks = json_decode($device['uplinks'], true);
                            if ($uplinks == null or empty($uplinks) or !$uplinks) {
                                $uplinks = [];
                            }
                            $uplinks = implode(',', $uplinks);
                        @endphp
                        <td>{{ $uplinks }}</td>

                        <td class="has-text-centered">
                            @if (Auth::user()->role == 'admin')
                                <a onclick="editUplinkModal('{{ $device->id }}', '{{ $device->name }}','{{ $uplinks }}')"
                                    class="button is-small is-info"><i class="fa-solid fa-gear"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
        </table>
    </div>

    @if (Auth::user()->role == 'admin')
        @include('modals.SwitchUplinkEditModal')
    @endif
    </x-layouts>
