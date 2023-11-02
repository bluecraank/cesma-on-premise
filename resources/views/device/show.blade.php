@section('title', $device->name)


<x-layouts.main>
<div x-data="{ fullwidth: false }">
    <div class="columns" x-show="!fullwidth">
        <div class="column">
            <div class="box">
                <p class="heading"><strong>{{ __('Last update') }}</strong>
                    <a @if ($device->active()) data-id="{{ $device->id }}" data-action="update" @else disabled @endif
                        class="is-pulled-right action">
                        <i class="is-hidden-touch mr-1 mdi mdi-update"></i> Update now
                    </a>
                </p>
                <p class="subtitle">
                    {{ Illuminate\Support\Carbon::parse($device->last_seen)?->diffForHumans() ?? 'Never' }}</p>
            </div>
        </div>


        <div class="column">
            <div class="box">
                <p class="heading"><strong>Vlans</strong>
                    <a href="{{ route('sync-vlans') }}" class="is-pulled-right is-fullwidth is-small is-success">
                        <i class="is-hidden-touch mr-1 mdi mdi-network"></i> Sync Vlans
                    </a>
                </p>
                <p class="subtitle">{{ $device->vlans->count() }}</p>
            </div>
        </div>


        <div class="column">
            <div class="box">
                <p class="heading"><strong>Uplinks</strong></p>
                <p class="subtitle">{{ $device->uplinks->count() }}</p>
            </div>
        </div>


        <div class="column">
            <div class="box">
                <p class="heading"><strong>Ports online</strong></p>
                <p class="subtitle">
                    {{ $device->ports->where('link', true)->count() }}/{{ $device->ports->count() - $device->uplinks->groupBy('name')->count() }}
                </p>
            </div>
        </div>

    </div>

    <div class="columns">
        <div class="column is-3" x-show="!fullwidth">
            @php
                $startDate = Illuminate\Support\Carbon::now()->subMilliseconds($device->uptime);
                $endDate = Illuminate\Support\Carbon::now();
                $days = $startDate->diffInDays($endDate);
                $hours = $startDate
                    ->copy()
                    ->addDays($days)
                    ->diffInHours($endDate);
                $minutes = $startDate
                    ->copy()
                    ->addDays($days)
                    ->addHours($hours)
                    ->diffInMinutes($endDate);
            @endphp

            <div class="columns">

                <div class="column">
                    <div x-cloak class="card" x-data="{ open: true }">
                        <div class="card-content p-3" x-show="open">

                            <div class="columns is-variable is-1">
                                <div class="column is-6">
                                    <button
                                        @if ($device->active()) data-id="{{ $device->id }}" data-action="backup" @else disabled @endif
                                        class="p-2 is-fullwidth button action is-small is-success">
                                        <i class="is-hidden-touch mr-1 mdi mdi-restore"></i> Create backup
                                    </button>
                                </div>
                                <div class="column is-6">
                                    <button
                                        @if ($enoughPubkeysToSync >= 2 && $device->active()) data-id="{{ $device->id }}" data-action="sync-pubkeys" @else disabled @endif
                                        class="p-2 is-fullwidth button action is-small is-success">
                                        <i class="is-hidden-touch mr-1 mdi mdi-key"></i> Sync pubkeys
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div x-cloak class="card has-table" x-data="{ open: true }">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-finance"></i></span>
                        {{ __('Summary') }}
                    </p>
                    <a class="card-header-icon">
                        <span class="icon" @click="open = !open"><i
                                x-bind:class="!open ? 'mdi-chevron-up' : 'mdi-chevron-down'" class="mdi"></i></span>
                    </a>
                </header>
                <div class="card-content" x-show="open">
                    <div class="b-table">
                        <div class="table-wrapper has-mobile-cards">
                            <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Hostname</td>
                                        <td class="">{{ $device->named ?? 'Unknown' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Online</td>
                                        <td><i
                                                class="mdi mdi-circle @if ($device->active()) has-text-success @else has-text-danger @endif"></i>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Uptime</td>
                                        <td class="">{{ $days }} days, {{ $hours }} hours,
                                            {{ $minutes }} minutes</td>
                                    </tr>

                                    <tr>
                                        <td>Firmware</td>
                                        <td class="">{{ $device->firmware ?? 'Unknown' }}</td>
                                    </tr>

                                    <tr>
                                        <td>Model</td>
                                        <td class="">{{ $device->model ?? 'Unknown' }}</td>
                                    </tr>

                                    <tr>
                                        <td>Type</td>
                                        <td class="">{{ $device->type_name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Site') }}</td>
                                        <td class="">{{ $device->site->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Building') }}</td>
                                        <td class="">{{ $device->building->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Room') }}</td>
                                        <td class="">{{ $device->room->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Location') }}</td>
                                        <td class="">{{ $device->location_description ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Synced vlans</td>
                                        <td class="">
                                            {{ $device->vlans()->count() }}/{{ App\Models\Vlan::where('site_id', Auth::user()->currentSite()->id)->get()->count() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Pubkey sync
                                        </td>
                                        <td>
                                            {{ $device->last_pubkey_sync ? Carbon\Carbon::parse($device->last_pubkey_sync)->diffForHumans() : 'Never' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ trans_choice('Clients', 2) }}</td>
                                        <td class="">{{ $device->clients->count() }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @livewire('device-uplinks', ['device' => $device])

            <div x-cloak class="card has-table" x-data="{ open: false }">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-network"></i></span>
                        Vlans
                    </p>
                    <a class="card-header-icon">
                        <span class="icon" @click="open = !open"><i
                                x-bind:class="!open ? 'mdi-chevron-up' : 'mdi-chevron-down'" class="mdi"></i></span>
                    </a>
                </header>
                <div class="card-content" x-show="open">
                    <div class="b-table">
                        <div class="table-wrapper has-mobile-cards">
                            <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th class="has-text-right">ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($device->vlans as $vlan)
                                        <tr>
                                            <td>{{ $vlan['name'] }}</td>
                                            <td class="has-text-right">{{ $vlan['vlan_id'] }}</td>
                                        </tr>
                                    @endforeach
                                    @if ($device->vlans->count() == 0)
                                        <tr>
                                            <td colspan="2" class="has-text-centered">{{ __('No vlans found') }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div x-cloak class="card has-table" x-data="{ open: false }">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-restore"></i></span>
                        Backups
                    </p>
                    <a class="card-header-icon">
                        <span class="icon" @click="open = !open"><i
                                x-bind:class="!open ? 'mdi-chevron-up' : 'mdi-chevron-down'" class="mdi"></i></span>
                    </a>
                </header>
                <div class="card-content" x-show="open">
                    <div class="b-table">
                        <div class="table-wrapper has-mobile-cards">
                            <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                                <thead>
                                    <tr>
                                        <th>{{ __('Created') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $device->backups = $device->backups->sortByDesc('created_at')->take(15);
                                    @endphp
                                    @foreach ($device->backups as $backup)
                                        <tr>
                                            <td>{{ $backup->created_at }}</td>
                                            <td class="has-text-right">
                                                {{ $backup->status == 1 ? __('Success') : __('Failed') }}</td>
                                        </tr>
                                    @endforeach

                                    @if ($device->backups->count() == 0)
                                        <tr>
                                            <td colspan="2" class="has-text-centered">
                                                {{ __('No backups created yet') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="column is-12-mobile is-12-tablet is-9-desktop" x-bind:class="fullwidth ? 'is-12-desktop' : ''">
            @php $ports = $device->ports->toArray(); @endphp
            @livewire('portoverview', ['ports' => $ports, 'device' => $device])
        </div>
    </div>
</div>

    @livewire('VlanTaggingModal')
    </x-layouts>
