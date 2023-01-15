<x-layouts.main>
    <div style="display:none" class="notification status is-danger">
        <ul>
            <li></li>
        </ul>
    </div>

    <div class="columns ml-1 mr-3">
        <div class="column">
            <div class="columns">
                <div class="column is-6">
                    <h1 class="title"><i class="fa fa-circle {{ $status }} online_status"></i> {{ $device->name }}</h1>

                    <h1 class="subtitle">
                        <i class="location_dot fa fa-location-dot"></i>
                        {{ $device->full_location }}
                    </h1>
                </div>
                <div class="column is-6">
                    <form action="/switch/refresh" id="refresh-form" method="post"><input type="hidden" class="device_id" name="id" value="{{ $device->id }}" />@csrf @method('PUT')<a onclick="refreshSwitch(this)" class="is-pulled-right is-primary button"><i class="is-size-5 fa fa-rotate"></i></a></form>
                </div>
            </div>
        </div>
    </div>

    <div class="level">
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>{{ __('Switch.Live.LastUpdate') }}</strong></p>
                <p class="subtitle">{{ $device->format_time }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>{{ __('Switch.Live.VlanSummary') }}</strong></p>
                <p class="subtitle">{{ $device->count_vlans }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>{{ __('Switch.Live.TrunkSummary') }}</strong></p>
                <p class="subtitle">{{ $device->count_trunks }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>Ports online</strong></p>
                <p class="subtitle">{{ $ports_online }}/{{ $count_ports }}</p>
            </div>
        </div>
    </div>

    <div class="columns ml-1 mr-3">

        <div class="column">
            <div class="box">
                <label class="label">Hostname</label>
                {{ $system->name }}
            </div>
        </div>

        <div class="column">
            <div class="box">
                <label class="label">{{ __('Switch.Live.Serialnumber') }}</label>
                {{ $system->serial }}
            </div>
        </div>
        <div class="column">
            <div class="box">
                <label class="label">Firmware</label>
                {{ $system->firmware }}
            </div>
        </div>
        <div class="column">
            <div class="box">
                <label class="label">Hardware</label>
                {{ $system->hardware }}
            </div>
        </div>
    </div>

    <div class="columns ml-1 mr-3">
        <div class="column is-4">
            <div class="box">
                <h2 class="subtitle">Trunks</h2>
                <table class="table is-striped is-narrow is-fullwidth">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>{{ __('Switch.Live.Members') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trunks as $key => $trunk)
                        <tr>
                            <td>{{ $key }}</td>
                            <td>{{ implode(', ', $trunk) }}</td>
                        </tr>
                        @endforeach
                        @if (empty($trunks))
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
                        @foreach(json_decode($device->vlan_data) as $vlan)
                        <tr class="is-clickable" onclick="location.href = '/vlans/{{ $vlan->vlan_id }}';">
                            <td>{{ $vlan->name }}</td>
                            <td>{{ $vlan->vlan_id }}</td>
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
                        @foreach($backups as $backup)
                        @php 
                            if($backup->status == 1) {
                                $status = __('Backup.Success');
                            } else {
                                $status = __('Backup.Failed');
                            }
                        @endphp
                        <tr class="is-clickable" onclick="location.href = '/switch/{{ $device->id }}/backups';">
                            <td>{{ $backup->created_at }}</td>
                            <td>{{ $status }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>

            <div class="box">
                <h2 class="subtitle">{{ __('Actions') }}</h2>

                <div class="buttons are-small">
                    <form class="mr-1" action="" id="actions-form" method="post"><input type="hidden" class="device_id" name="id" value="{{ $device->id }}" />
                        @csrf
                        <a onclick="device_live_actions(this, 'pubkeys')" class="button is-success">
                            <i style="margin-right: 7px;" class="fa-solid fa-sync"></i> Sync Pubkeys
                        </a>

                        <a onclick="device_live_actions(this, 'backups')" class="button is-success">
                            <i style="margin-right: 7px;" class="fa-solid fa-hdd"></i> Create Backup
                        </a>
                    </form>
                  </div>
            </div>
        </div>


        <div class="column is-8">
            <div class="box">
                <h2 class="subtitle">{{ __('Switch.Live.Portoverview') }} 
                    <span onclick="$('.port-vlan-select').each(function() {
                        $( this ).prop('disabled', true);
                      });$('.save-vlans').addClass('is-hidden');$('.edit-vlans').removeClass('is-hidden');" class="ml-3 hover-underline save-vlans is-hidden is-pulled-right is-size-7 is-clickable">Abbrechen</span>
                <span onclick="updateUntaggedPorts('{{ $device->id }}')" class="ml-3 hover-underline save-vlans is-hidden is-pulled-right is-size-7 is-clickable">Speichern</span>
                <span onclick="$('.port-vlan-select').each(function() {
                    $( this ).prop('disabled', false);
                  });$('.save-vlans').removeClass('is-hidden');$('.edit-vlans').addClass('is-hidden');
                   " class="hover-underline is-pulled-right is-size-7 edit-vlans is-clickable">Bearbeiten</span>
                   
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
                            <th>Untagged</th>
                            <th>Tagged</th>
                            <th class="has-text-centered">Speed Mbit/s</th>
                        </tr>
                    </thead>

                    <tbody class="live-body">
                        @php
                            $portlist = $device->ports;
                            sort($portlist);

                        @endphp
                        @foreach ($portlist as $port)
                        @if (!str_contains($port['id'], "Trk"))

                        @php
                        $status = ($port['is_port_up']) ? 'has-text-success' : 'has-text-danger';
                        if($port['trunk_group'] != null) {
                        $tagged[$port['id']] = $tagged[$port['trunk_group']];
                        }
                        @endphp

                        <tr style="line-height: 37px;">
                            <td class="has-text-centered">
                                <i class="fa fa-circle {{ $status }}"></i>
                            </td>
                            <td class="has-text-centered">
                                {{ $port['id'] }}
                            </td>
                            <td>
                                {{ $port['name'] }}
                            </td>
                            <td>
                                @if(str_contains($untagged[$port['id']], 'Trk')) 
                                {{ $untagged[$port['id']] }}
                                @else
                                <div class="select">
                                    <select data-id="{{ $device->id }}" data-port="{{ $port['id'] }}" data-current-vlan="{{  ($untagged[$port['id']]) ? $untagged[$port['id']] : 0 }}" class="port-vlan-select" disabled>
                                        <option value="0">Kein VLAN</option>
                                        @foreach(json_decode($device->vlan_data) as $vlan)
                                        {{ $untagged[$port['id']] }}
                                        <option value="{{ $vlan->vlan_id }}" {{  ($untagged[$port['id']] == $vlan->vlan_id) ? 'selected' : '' }}>{{ $vlan->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown is-down is-small">
                                    <div class="dropdown-trigger" onclick="$(this).parent().toggleClass('is-active');">
                                        <button class="button" aria-haspopup="true" aria-controls="dropdown-menu7">
                                            <span> {{ count($tagged[$port['id']]) }} VLANs</span>
                                            <span class="icon is-small">
                                                <i class="fas fa-angle-up" aria-hidden="true"></i>
                                            </span>
                                        </button>
                                    </div>
                                    <div class="dropdown-menu" id="dropdown-menu7" role="menu">
                                        <div class="dropdown-content">
                                            <div class="dropdown-item">
        
                                                <div class="tags">
                                                    @php sort($vlans) @endphp
                                                    @foreach ($vlans as $vlan)
                                                
                                                    <span class="tag is-blue is-clickable has-text-weight-bold has-text-white"><input type="checkbox" {{ (in_array($vlan['vlan_id'], $tagged[$port['id']])) ? 'checked' : '' }} name="selectednames[]" value="4" class="mr-2" /> {{ $vlan['vlan_id'] }}</span>
                                                    
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="has-text-centered">
                                @if ($port_statistic[$port['id']]['port_speed_mbps'] == 0)
                                <span class="tag is-link ">{{ $port_statistic[$port['id']]['port_speed_mbps'] }}</span>
                                @elseif ($port_statistic[$port['id']]['port_speed_mbps'] == 10)
                                <span class="tag is-danger ">{{ $port_statistic[$port['id']]['port_speed_mbps'] }}</span>
                                @elseif ($port_statistic[$port['id']]['port_speed_mbps'] == 100)
                                <span class="tag is-warning">{{ $port_statistic[$port['id']]['port_speed_mbps'] }}</span>
                                @elseif ($port_statistic[$port['id']]['port_speed_mbps'] == 1000)
                                <span class="tag is-primary">{{ $port_statistic[$port['id']]['port_speed_mbps'] }}</span>
                                @elseif ($port_statistic[$port['id']]['port_speed_mbps'] == 10000)
                                <span class="tag is-primary">{{ $port_statistic[$port['id']]['port_speed_mbps'] }}</span>
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
    </x-layouts>