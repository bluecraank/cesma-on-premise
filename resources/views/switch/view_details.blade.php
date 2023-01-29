<x-layouts.main>
    @inject('cc', 'App\Http\Controllers\ClientController')
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
                        {{ $device->location()->first()->name }}
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
                <p class="subtitle">{{ count($device->vlans()->get()) }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>{{ __('Switch.Live.TrunkSummary') }}</strong></p>
                <p class="subtitle">{{ count($uplinks) }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>Ports online</strong></p>
                <p class="subtitle">{{ count($device->portsOnline()) }}/{{ count($ports) - count($uplinks) }}</p>
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
    </div>

    <div class="columns ml-1 mr-3">
        <div class="column is-4">
            @if (Auth::user()->role == 'admin')
                <div class="box">
                    <h2 class="subtitle">{{ __('Actions') }}</h2>
                    <div class="buttons are-small">
                        <a onclick="sw_actions(this, 'refresh', {{ $device->id }})" class="is-success button">
                            <i class="mr-2 fas fa-sync"></i> Refresh
                        </a>

                        <a onclick="sw_actions(this, 'backups', {{ $device->id }})" class="button is-success">
                            <i class="mr-2 fas fa-hdd"></i> Backup
                        </a>

                        <a onclick="sw_actions(this, 'pubkeys', {{ $device->id }})" class="button is-success">
                            <i class="mr-2 fas fa-key"></i> Sync Pubkeys
                        </a>

                        <a onclick="$('.modal-sync-vlans-specific').show();" class="button is-success">
                            <i class="mr-2 fas fa-ethernet"></i> Sync Vlans
                        </a>
                    </div>
                </div>
            @endif

            <div class="box">
                <h2 class="subtitle">Uplinks</h2>
                <table class="table is-striped is-narrow is-fullwidth">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>{{ __('Switch.Live.Members') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Sort uplinks in correct order
                            uksort($uplinks, 'strnatcmp');
                        @endphp
                        @foreach ($uplinks as $key => $trunk)
                            @php
                                $portsById = $device
                                    ->ports()
                                    ->get()
                                    ->keyBy('id');
                                $trunks = [];
                                foreach ($trunk as $port) {
                                    $trunks[$port['device_port_id']] = $portsById[$port['device_port_id']]->name ?? 'Unknown';
                                }
                            @endphp
                            <tr>
                                <td>{{ $key }}</td>
                                <td>{{ implode(', ', $trunks) }}</td>
                            </tr>
                        @endforeach

                        @if (empty($uplinks))
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
                            <th>ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Sort vlans in correct order
                            $vlanlist = $vlans->toArray();
                            uksort($vlanlist, 'strnatcmp');
                        @endphp
                        @foreach ($vlanlist as $vlan)
                            <tr>
                                <td>{{ $vlan['name'] }}</td>
                                <td>{{ $vlan['vlan_id'] }}</td>
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
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($backups as $backup)
                            <tr>
                                <td>{{ $backup->created_at }}</td>
                                <td>{{ $backup->status == 1 ? __('Backup.Success') : __('Backup.Failed') }}</td>
                            </tr>
                        @endforeach

                        @if (count($backups) == 0)
                            <tr>
                                <td colspan="2">Kein Backup bisher durchgeführt</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

            </div>
        </div>


        <div class="column is-8">
            @if (Auth::user()->role == 'admin')
                <script>
                    function enableEditing() {
                        $('.port-vlan-select').each(function() {
                            $(this).prop('disabled', false);
                        });
                        $('.save-vlans').removeClass('is-hidden');
                        $('.edit-vlans').addClass('is-hidden');
                        $('.modal-vlan-tagging').find('.is-submit').prop('disabled', false);
                    }

                    function disableEditing() {
                        $('.port-vlan-select').each(function() {
                            $(this).prop('disabled', true);
                        });
                        $('.save-vlans').addClass('is-hidden');
                        $('.edit-vlans').removeClass('is-hidden');
                        $('.modal-vlan-tagging').find('.is-submit').prop('disabled', true);
                    }
                </script>
            @endif
            <div class="box">
                <h2 class="subtitle">{{ __('Switch.Live.Portoverview') }}
                    @if (Auth::user()->role == 'admin')
                        <span onclick="disableEditing();"
                            class="ml-3 hover-underline save-vlans is-hidden is-pulled-right is-size-7 is-clickable">{{ __('Button.Cancel') }}</span>
                        <span onclick="updateUntaggedPorts('{{ $device->id }}')"
                            class="ml-3 hover-underline save-vlans is-hidden is-pulled-right is-size-7 is-clickable">{{ __('Button.Save') }}</span>
                        <span onclick="enableEditing();"
                            class="hover-underline is-pulled-right is-size-7 edit-vlans is-clickable">{{ __('Button.Edit') }}</span>
                    @endif
                </h2>

                <div class="notification response-update-vlan is-hidden is-success">
                    <button class="delete" onclick="$('.response-update-vlan').addClass('is-hidden');"></button>
                    <span class="response-update-vlan-text"></span>
                </div>


                <table class="table is-striped is-narrow is-fullwidth">
                    <thead>
                        <tr>
                            <th class="has-text-centered" style="width: 70px;">Status</th>
                            <th class="has-text-centered" style="width: 70px;">Port</th>
                            <th>{{ __('Switch.Live.Portname') }}</th>
                            <th class="has-text-centered">Untagged</th>
                            <th class="has-text-centered" style="width:130px">Tagged</th>
                            <th class="has-text-centered" style="width: 150px;">{{ __('Clients') }}</th>
                            <th class="has-text-centered" style="width: 80px;">Speed Mbit/s</th>
                        </tr>
                    </thead>

                    <tbody class="live-body">
                        @php
                            $portsByName = $portsByName->sort(function ($a, $b) {
                                return strnatcmp($a['name'], $b['name']);
                            });
                        @endphp
                        @foreach ($portsByName as $id => $port)
                            @if (!str_contains($port['name'], 'Trk'))
                                <tr style="line-height: 37px;">
                                    <td class="has-text-centered">
                                        <i
                                            class="fa fa-circle {{ $port['link'] ? 'has-text-success' : 'has-text-danger' }}"></i>
                                    </td>
                                    <td class="has-text-centered">
                                        <a
                                            href="/switch/{{ $device->id }}/ports/{{ $port['name'] }}">{{ $port['name'] }}</a>

                                    </td>
                                    <td>
                                        {{ $port['description'] }}
                                    </td>
                                    <td class="has-text-centered" style="width:110px">
                                        @if ($port->isMemberOfTrunk())
                                            <span class="tag is-info">{{ $port->trunkName() }}</span>
                                        @else
                                            <div class="select is-small">
                                                <select disabled data-id="{{ $device->id }}"
                                                    data-port="{{ $port->name }}"
                                                    data-current-vlan="{{ $port->untaggedVlan() ? $port->untaggedVlan() : 0 }}"
                                                    class="port-vlan-select" name="" id="">
                                                    <option value="0">No VLAN</option>
                                                    @foreach ($vlans as $vlan)
                                                        <option value="{{ $vlan->id }}"
                                                            {{ $port->untaggedVlan() == $vlan->id ? 'selected' : '' }}>
                                                            {{ $vlan->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="has-text-centered" style="width:130px">
                                        @if ($port->isMemberOfTrunk())
                                            {{ count($vlanPortsTagged[$portsByName[$port->trunkName()]->id]) }} VLANs
                                        @else
                                            <a
                                                onclick="updateTaggedModal('{{ implode(',',$port->taggedVlans()->pluck('device_vlan_id')->toArray()) }}', '{{ $port['id'] }}', '{{ $device->id }}')">{{ count(isset($vlanPortsTagged[$port['id']]) ? $vlanPortsTagged[$port['id']]->toArray() : []) ?? 'No VLAN' }}
                                                VLANs</a>
                                        @endif
                                    </td>
                                    <td class="has-text-centered" style="width: 150px;">
                                        @if ($port->isMemberOfTrunk())
                                            <span class="tag is-warning">Excluded (Uplink)</span>
                                        @else
                                            <div class="dropdown is-hoverable">
                                                <div class="dropdown-trigger">
                                                    <button class="button" aria-haspopup="true"
                                                        aria-controls="dropdown-menu4">
                                                        <span>
                                                            {{ isset($clients[$port['name']]) ? count($clients[$port['name']]) : '0' }}
                                                            Clients
                                                        </span>
                                                        <span class="icon is-small">
                                                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                                                        </span>
                                                    </button>
                                                </div>
                                                <div class="dropdown-menu" style="min-width:15rem" id="dropdown-menu4" role="menu">
                                                    <div class="dropdown-content">
                                                        <div class="dropdown-item has-text-left">
                                                            @if (isset($clients[$port['name']]))
                                                                @foreach ($clients[$port['name']] as $client)
                                                                    <i
                                                                        class="fa-solid {{ $cc::getClientIcon($client['type']) }} mr-1"></i>
                                                                    {{ $client['hostname'] }}
                                                                    <br>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="has-text-centered" style="width: 80px;">
                                        @if ($port->speed == 0)
                                            <span class="tag is-link ">{{ $port->speed }}</span>
                                        @elseif ($port->speed == 10)
                                            <span class="tag is-danger ">{{ $port->speed }}</span>
                                        @elseif ($port->speed == 100)
                                            <span class="tag is-warning">{{ $port->speed }}</span>
                                        @elseif ($port->speed == 1000)
                                            <span class="tag is-primary">{{ $port->speed }}</span>
                                        @elseif ($port->speed == 10000)
                                            <span class="tag is-success">{{ $port->speed }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('modals.VlanTaggingModal')
    @include('modals.SwitchSyncVlansModal')

    </x-layouts>
