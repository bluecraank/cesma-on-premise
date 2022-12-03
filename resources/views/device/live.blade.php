<x-layouts.main>
    @if ($errors->any())
    <div class="notification is-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session()->has('success'))
    <div class="notification is-success">
        {{ session()->get('success') }}
    </div>
    @endif
    <div class="columns">
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
                    <form action="/switch/refresh" method="post"><input type="hidden" name="id" value="{{ $device->id }}" />@csrf @method('PUT')<button class="is-pulled-right is-primary button"><i class="is-size-5 fa fa-rotate"></i></button></form>
                </div>
            </div>
        </div>
    </div>

    <div class="level">
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>Aktualsiert</strong></p>
                <p class="subtitle">{{ $device->format_time }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>VLANS INSGESAMT</strong></p>
                <p class="subtitle">{{ $device->count_vlans }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>TRUNKS INSGESAMT</strong></p>
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
                <label class="label">Seriennummer</label>
                {{ $system->serial_number }}
            </div>
        </div>
        <div class="column">
            <div class="box">
                <label class="label">Firmware</label>
                {{ $system->firmware_version }}
            </div>
        </div>
        <div class="column">
            <div class="box">
                <label class="label">Hardware</label>
                {{ $system->hardware_revision }}
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
                            <th>Ports</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trunks as $key => $trunk)
                        <tr>
                            <td>{{ $key }}</td>
                            <td>{{ implode(', ', $trunk) }}</td>
                        </tr>
                        @endforeach
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
                        @foreach(json_decode($device->vlan_data)->vlan_element as $vlan)
                        <tr>
                            <td>{{ $vlan->name }}</td>
                            <td>{{ $vlan->vlan_id }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>


        <div class="column is-8">
            <div class="box">
                <h2 class="subtitle"><span class='success_font'>LIVE</span> Portübersicht</h2>
                <table class="table is-striped is-narrow is-fullwidth">
                    <thead>
                        <tr>
                            <th class="has-text-centered" style="width: 70px;">Status</th>
                            <th class="has-text-centered" style="width: 70px;">Port</th>
                            <th>Name</th>
                            <th>Untagged</th>
                            <th>Tagged</th>
                            <th>Speed</th>
                        </tr>
                    </thead>

                    <tbody class="live-body">
                        @foreach ($device->ports as $port)
                        @if (!str_contains($port->id, "Trk"))

                        @php
                        $status = ($port->is_port_up) ? 'is-online' : 'is-offline';
                        if($port->trunk_group != null) {
                            $tagged[$port->id] = $tagged[$port->trunk_group];
                        }
                        @endphp
                        <tr>
                            <td class="has-text-centered">
                                <i class="fa fa-circle {{ $status }}"></i>
                            </td>
                            <td class="has-text-centered">
                                {{ $port->id }}
                            </td>
                            <td>
                                {{ $port->name }}
                            </td>
                            <td>
                                {{ $untagged[$port->id] }}
                            </td>
                            <td>
                                <div class='dropdown is-hoverable'>
                                    <div class='dropdown-trigger'>
                                        <button class='button' aria-haspopup='true' aria-controls='dropdown-menu4'>
                                            <span>{{ count($tagged[$port->id]) }}</span><span class='icon is-small'>
                                                <i class='fas fa-angle-down' aria-hidden='true'></i>
                                            </span>
                                        </button>
                                    </div>
                                    <div class='dropdown-menu' id='dropdown-menu4' role='menu'>
                                        <div class='dropdown-content'>
                                            <div class='dropdown-item'>
                                                @foreach ($tagged[$port->id] as $tag)
                                                <span class="tag is-link">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ $port_statistic[$port->id]['port_speed_mbps'] }} Mbit/s
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