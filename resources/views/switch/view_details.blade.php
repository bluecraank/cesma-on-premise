@section('title', $device->name)


<x-layouts.main>
    <script>
        window.device_id = {{ $device->id }};
        window.timestamp = '{{ $device->updated_at }}'
        window.msgnothingchanged = '{{ __('Msg.NothingChanged') }}'
    </script>
    @inject('cc', 'App\Services\ClientService')

    <div class="columns ml-1 mr-3">
        <div class="column">
            <div class="columns">
                <div class="column is-6">
                    <h1 class="title"><i
                            class="fa fa-circle {{ $is_online ? 'has-text-success' : 'has-text-danger' }} online_status"></i>
                        {{ $device->name }}
                    </h1>

                    <h1 class="subtitle">
                        <i class="location_dot fa fa-location-dot"></i>
                        {{ $device->location()->first()->name }} - {{ $device->building()->first()->name }} -
                        {{ $device->room()->first()->name }} #{{ $device->location_number }}
                    </h1>
                </div>
                <div class="column is-6">
                </div>
            </div>
        </div>
    </div>

    <div class="level">
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>{{ __('Switch.Live.LastUpdate') }}</strong></p>
                <p class="subtitle">{{ $device->updated_at->diffForHumans() }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>{{ __('Switch.Live.VlanSummary') }}</strong></p>
                <p class="subtitle">{{ $device->vlans->count() }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>{{ __('Switch.Live.TrunkSummary') }}</strong></p>
                <p class="subtitle">{{ $device->uplinks->count() }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>Ports online</strong></p>
                <p class="subtitle">
                    {{ $device->ports->where('link', true)->count() }}/{{ $device->ports->count() - $device->uplinks->groupBy('name')->count() }}
                </p>
            </div>
        </div>
    </div>

    <div class="columns ml-1 mr-3">

        <div class="column">
            <div class="box">
                <label class="label">Hostname</label>
                {{ $device->named }}
            </div>
        </div>

        <div class="column">
            <div class="box">
                <label class="label">{{ __('Switch.Live.Serialnumber') }}</label>
                {{ $device->serial }}
            </div>
        </div>
        <div class="column">
            <div class="box">
                <label class="label">Firmware</label>
                {{ $device->firmware }}
            </div>
        </div>
        <div class="column">
            <div class="box">
                <label class="label">Hardware</label>
                {{ $device->hardware }}
            </div>
        </div>
        <div class="column">
            <div class="box">
                <label class="label">TYP</label>
                {{ $device->type_name }}
            </div>
        </div>
    </div>

    <div class="columns ml-1 mr-3">
        <div class="column is-3">
            @if (Auth::user()->role >= 1)
                <div class="box">
                    <h2 class="subtitle">{{ __('Actions') }}</h2>
                    <div class="columns is-variable is-multiline">
                        <div class="column has-text-centered is-narrow is-6 is-12-tablet col-md-4 pb-0">
                            <button onclick="$('.modal-sync-vlans-specific').show();"
                                class="p-1 m-0 is-fullwidth button is-small is-success">
                                <i class="is-hidden-touch mr-1 fas fa-ethernet"></i> Sync Vlans
                            </button>
                        </div>
                        <div class="column is-narrow is-6 is-12-tablet col-md-4 pb-0">
                            <button onclick="sw_actions(this, 'pubkeys', {{ $device->id }})"
                                class="p-1 m-0 is-fullwidth button is-small is-success">
                                <i class="is-hidden-touch mr-1 fas fa-key"></i> Sync Pubkeys
                            </button>
                        </div>
                        <div class="column is-narrow is-12 col-md-4 pb-0">
                            <button onclick="sw_actions(this, 'backups', {{ $device->id }})"
                                class="p-1 m-0 is-fullwidth button is-small is-success">
                                <i class="is-hidden-touch mr-1 fas fa-hdd"></i> Backup
                            </button>
                        </div>

                        <div class="column is-narrow is-12 col-md-4 pb-4">
                            <button onclick="sw_actions(this, 'refresh', {{ $device->id }})"
                                class="p-1 m-0 is-fullwidth is-success button is-small">
                                <i class="is-hidden-touch mr-1 fas fa-sync"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="box">
                <h2 class="subtitle">{{ __('Uplinks found') }}</h2>
                <table class="table is-striped is-narrow is-fullwidth">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="has-text-right">{{ __('Switch.Live.Members') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($found_uplinks as $trunk => $ports_in_trunk)
                            <tr>
                                <td>{{ $trunk }}</td>
                                <td class="has-text-right">{{ $ports_in_trunk }}</td>
                            </tr>
                        @endforeach

                        @if ($device->uplinks->count() == 0)
                            <tr>
                                <td colspan="2">{{ __('Switch.Live.NoTrunksFound') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="box">
                <h2 class="subtitle">{{ __('Custom Uplinks') }}</h2>
                <table class="table is-striped is-narrow is-fullwidth">
                    <thead>
                        <tr>
                            <th>{{ __('Description') }}</th>
                            <th class="has-text-right">Port</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($custom_uplinks_array))

                            @foreach ($custom_uplinks_array as $port)
                                <tr>
                                    <td>{{  $device->ports->where('name', $port)->first()->description ?? $port }}</td>
                                    <td class="has-text-right">{{ $port }}</td>
                                </tr>
                            @endforeach
                        @endif

                        @if (empty($custom_uplinks_array))
                            <tr>
                                <td colspan="2">{{ __('Switch.Live.NoTrunksFound') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="box">
                <h2 class="subtitle">VLANs</h2>
                <table class="table is-striped is-narrow is-fullwidth">
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
                    </tbody>
                </table>

            </div>
            <div class="box">
                <h2 class="subtitle">Backups</h2>
                <table class="table is-striped is-narrow is-fullwidth">
                    <thead>
                        <tr>
                            <th>{{ __('Backup.Created') }}</th>
                            <th class="has-text-right">Status</th>
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
                                    {{ $backup->status == 1 ? __('Backup.Success') : __('Backup.Failed') }}</td>
                            </tr>
                        @endforeach

                        @if ($device->backups->count() == 0)
                            <tr>
                                <td colspan="2">Bisher kein Backup durchgeführt</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

            </div>
        </div>


        <div class="column is-12">
            <div class="box">
                <h2 class="subtitle">{{ __('Switch.Live.Portoverview') }}
                    @if (Auth::user()->role >= 1)
                        <button onclick="saveEditedPorts(this);"
                            class="is-save-button button is-small is-success is-pulled-right is-hidden"><i
                                class="fas fa-save mr-2"></i> {{ __('Button.Save') }}</button>

                        <button onclick="cancelEditing(this);"
                            class="is-save-button button is-small is-link is-pulled-right is-hidden mr-2"><i
                                class="fas fa-xmark mr-2"></i> {{ __('Button.Cancel') }}</button>

                        <button
                            onclick="editUplinkModal('{{ $device->id }}', '{{ $device->name }}','{{ $custom_uplinks_comma_seperated }}')"
                            class="is-save-button button is-small is-info is-pulled-right is-hidden mr-2"><i
                                class="fas fa-up-down mr-2"></i> Uplinks</button>

                        {{-- <button class="is-save-button button is-small is-info is-pulled-right is-hidden mr-2"><i class="fas fa-file-pen mr-2"></i> {{ __('Button.Bulkedit') }}</button> --}}

                        <button onclick="enableEditing();"
                            class="is-edit-button button is-small is-info is-pulled-right"><i
                                class="fas fa-edit mr-2"></i> {{ __('Button.Edit') }}</button>
                    @endif
                </h2>

                <div class="notification response-update-vlan is-hidden is-success">
                    <button class="delete" onclick="$('.response-update-vlan').addClass('is-hidden');"></button>
                    <span class="response-update-vlan-text"></span>
                </div>


                <div class="table-container">
                <table id="portoverview" class="table is-striped is-narrow is-fullwidth">
                    <thead>
                        <tr>
                            <th class="has-text-centered" style="width: 45px;">Status</th>
                            <th class="has-text-centered" style="width: 60px;">Port</th>
                            <th>{{ __('Switch.Live.Portname') }}</th>
                            <th>Untagged/Native</th>
                            <th>Tagged/Allowed</th>
                            <th class="has-text-left">{{ trans_choice('Clients', 2) }}</th>
                            <th class="has-text-centered" style="max-width: 120px;">Speed</th>
                        </tr>
                    </thead>

                    <tbody class="live-body">
                        @foreach ($device->ports as $port)
                            @if (!str_contains($port->name, 'Trk'))
                                @livewire('port', ['clients' => $device->clients->where('port_id', $port->name), 'device_id' => $device->id, 'vlans' => $device->vlans, 'vlanports' => $device->vlanports->where('device_port_id', $port->id), 'port' => $port, 'cc' => $cc])
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
    @include('modals.VlanTaggingModal')
    @include('modals.SwitchSyncVlansModal')
    @include('modals.PortBulkEditVlansModal')
    @include('modals.SwitchUplinkEditModal')
    </x-layouts>
