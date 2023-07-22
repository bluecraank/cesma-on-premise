@section('title', 'Port '.$current_port->name . ' on ' . $device->name)

<x-layouts.main>
    <div class="columns ml-1 mr-3">
        <div class="column">
            <div class="columns">
                <div class="column is-6">
                    <h1 class="title">
                        Port {{ $current_port->name }} on Switch {{ $device->name }}
                    </h1>

                    <h1 class="subtitle">
                        {{ __('Portstats.for') }}
                    </h1>
                </div>

                <div class="column is-6 mt-2">
                    <div class="select is-small is-pulled-right">
                        <select onchange="location.href='?timespan='+$(this).val()">
                            <option {{ app('request')->input('timespan') == '15' ? 'selected' : '' }} value="15">15 minutes</option>
                            <option {{ app('request')->input('timespan') == '30' ? 'selected' : '' }} value="30">30 minutes</option>
                            <option {{ app('request')->input('timespan') == '60' ? 'selected' : '' }} value="60">60 minutes</option>
                            <option {{ app('request')->input('timespan') == '120' ? 'selected' : '' }} value="120">2 hours</option>
                            <option {{ app('request')->input('timespan') ?? 'selected' }} {{ app('request')->input('timespan') == '180' ? 'selected' : '' }} value="180">3 hours</option>
                            <option {{ app('request')->input('timespan') == '360' ? 'selected' : '' }} value="360">6 hours</option>
                            <option {{ app('request')->input('timespan') == '720' ? 'selected' : '' }} value="720">12 hours</option>
                            <option {{ app('request')->input('timespan') == '1440' ? 'selected' : '' }} value="1440">24 hours</option>
                        </select>
                    </div>
                    <div class="select is-small is-pulled-right mr-3">
                        <select onchange="location.href='/device/{{ $device->id }}/ports/'+$(this).val()+'?timespan={{ app('request')->input('timespan') }}'">
                            @if ($port_id == null)
                                <option value="" selected>Bitte wählen</option>
                            @endif
                            @foreach ($ports as $port)
                                @if (str_contains($port['name'], 'Trk'))
                                    @continue
                                @endif
                                <option {{ $port['name'] == $port_id ? 'selected' : '' }} value="{{ $port['name'] }}">
                                    Port {{ $port['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="is-pulled-right mr-3">
                        <a class="button is-small is-info" href="/device/{{ $device->id }}">{{ __('Button.Back', ["device" => $device->name]) }}</a>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <nav class="level">
        <div class="level-item has-text-centered">
          <div>
            <p class="heading">ALIAS</p>
            <p class="title">{{  ($current_port->description != '') ? $current_port->description : 'No alias' }}</p>
          </div>
        </div>
        <div class="level-item has-text-centered">
          <div>
            <p class="heading">Last check</p>
            <p class="title">{{ $last_stat->created_at->diffForHumans() }}</p>
          </div>
        </div>
      </nav>

    <div class="columns ml-1 mr-3">
        <div class="column">
            <div class="box has-text-centered">
                <label class="label">STATUS</label>
                @if ($last_stat->port_status)
                    <span class="is-size-2 has-text-success">UP</span>
                @else
                    <span class="is-size-2 has-text-danger">DOWN</span>
                @endif
            </div>
        </div>
        <div class="column">
            <div class="box has-text-centered">
                <label class="label">SPEED</label>
                @if ($port_stats->last()->port_speed == 0)
                    <span class="is-size-2 has-text-link" style="width: 100%;">0</span>
                @elseif ($port_stats->last()->port_speed == 10)
                    <span class="is-size-2 has-text-danger"style="width: 100%;">10 Mbit/s</span>
                @elseif ($port_stats->last()->port_speed == 100)
                    <span class="is-size-2 has-text-warning"style="width: 100%;">100 Mbit/s</span>
                @elseif ($port_stats->last()->port_speed == 1000)
                    <span class="is-size-2 has-text-success"style="width: 100%;">1 Gbit/s</span>
                @elseif ($port_stats->last()->port_speed == 10000)
                    <span class="is-size-2" style="width:100%;color:chartreuse;">10 Gbit/s</span>
                @endif
            </div>
        </div>
        <div class="column">
            <div class="box has-text-centered">
                <label class="label">MODE</label>
                <span class="is-size-2 has-text-info">{{ $current_port->vlan_mode }}</span>
            </div>
        </div>
    </div>

    <div>
        <div class="columns is-multiline ml-1 mr-3">
            <div class="column is-4">
                <div class="box">
                    <h2 class="subtitle">Mbit/s (RX/TX)</h2>
                    <canvas id="port_rx_tx_bps"></canvas>
                </div>
            </div>

            <div class="column is-4">
                <div class="box">
                    <h2 class="subtitle">Packete (RX/TX)</h2>
                    <canvas id="port_rx_tx_packets"></canvas>
                </div>
            </div>

            <div class="column is-4">
                <div class="box">
                    <h2 class="subtitle">Mbit (RX/TX)</h2>
                    <canvas id="port_rx_tx_bytes"></canvas>
                </div>
            </div>
        </div>

        <div class="columns is-multiline ml-1 mr-3">
            <div class="column is-6">
                <div class="box">
                    <h2 class="subtitle">Utilization RX</h2>
                    <div class="columns is-multiline">
                        <div class="is-12 column">
                            <label class="label">Lastest load: {{ $utilization_rx }}%</label>
                            <progress class="progress is-primary" value="{{ $utilization_rx }}"
                                max="{{ $speed }}">{{ $utilization_rx }}</progress>
                        </div>

                        <div class="is-12 column">
                            <label class="label">Average load: {{ $avg_utilization_rx }}%</label>
                            <progress class="progress is-info" value="{{ $avg_utilization_rx }}"
                                max="{{ $speed }}">{{ $avg_utilization_rx }}</progress>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-6">   
                <div class="box">
                    <h2 class="subtitle">Utilization TX</h2>
                    <div class="columns is-multiline">
                        <div class="is-12 column">
                            <label class="label">Lastest load: {{ $utilization_tx }}%</label>
                            <progress class="progress is-primary" value="{{ $utilization_tx }}"
                                max="{{ $speed }}">{{ $utilization_tx }}</progress>
                        </div>

                        <div class="is-12 column">
                            <label class="label">Average load: {{ $avg_utilization_tx }}%</label>
                            <progress class="progress is-info" value="{{ $avg_utilization_tx }}"
                                max="{{ $speed }}">{{ $avg_utilization_tx }}</progress>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="columns is-multiline ml-1 mr-3">
            <div class="column is-6">
                <div class="box">
                    <h2 class="subtitle">Native / Untagged VLANs</h2>
                    <span class="tag is-primary m-1">{{ ($current_port->untagged) ? $current_port->untagged->name : 'NO VLAN' }}</span>
                </div>
            </div>
        
            <div class="column is-6">
                <div class="box">
                    <h2 class="subtitle">Allowed / Tagged VLANs</h2>
                    @php $vlanports = $current_port->tagged; @endphp
                    @foreach($vlanports as $vlan)
                        <span title="ID {{ $vlan['vlan_id'] }}" class="tag is-primary m-1">{{ $vlan['name'] }}</span>
                    @endforeach

                    @if ($current_port->vlan_mode == "access")
                        <span class="tag is-info">{{ __('Port.Access.NoAllowedVlans') }}</span>
                    @elseif($device->vlanports->where('device_port_id', $current_port->id)->count() == 0)
                        <span class="tag is-info">{{ __('Port.NativeUntagged.AllVlansAllowed') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        localStorage.getItem('theme') == 'dark' ? Chart.defaults.color = '#fff' : Chart.defaults.color = '#000';

        const data = {!! $dataset !!}

        const ctx = document.getElementById('port_rx_tx_bps');
        const cfg_chr_RX_TX = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
            },
        };

        new Chart(ctx, cfg_chr_RX_TX);

        const data2 = {!! $dataset2 !!}

        const ctx2 = document.getElementById('port_rx_tx_packets');
        const cfg_chr_RX_TX2 = {
            type: 'line',
            data: data2,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
            },
        };

        new Chart(ctx2, cfg_chr_RX_TX2);

        const data3 = {!! $dataset3 !!}

        const ctx3 = document.getElementById('port_rx_tx_bytes');
        const cfg_chr_RX_TX3 = {
            type: 'line',
            data: data3,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
            },
        };

        new Chart(ctx3, cfg_chr_RX_TX3);
    </script>
    </x-layouts>
