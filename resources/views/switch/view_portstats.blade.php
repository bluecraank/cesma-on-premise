<x-layouts.main>
    <div class="columns ml-1 mr-3">
        <div class="column">
            <div class="columns">
                <div class="column is-6">
                    <h1 class="title">
                        {{ $device->name }}
                    </h1>

                    <h1 class="subtitle">
                        {{ __('Portstats.for') }} {{ $port_id }}
                    </h1>
                </div>

                <div class="column is-6">
                    <div class="select is-pulled-right">
                        <select onchange="location.href='/switch/{{ $device->id }}/ports/'+$(this).val()">
                            @if ($port_id == null)
                                <option value="" selected>Bitte wählen</option>
                            @endif
                            @foreach ($ports as $port)
                                @if (str_contains($port['name'], 'Trk'))
                                    @continue
                                @endif
                                <option {{ $port['name'] == $port_id ? 'selected' : '' }} value="{{ $port['name'] }}">
                                    {{ $port['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="is-pulled-right mr-3">
                        <a class="button is-info" href="/switch/{{ $device->id }}">{{ __('Button.Back') }}</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="columns ml-1 mr-3">

        <div class="column">
            <div class="box has-text-centered">
                <label class="label">LINK STATUS</label>
                {!! ($current_port->link == 1)
                    ? '<span class="is-size-2 has-text-success">UP</span>'
                    : '<span class="is-size-2 has-text-danger">DOWN</a>' !!}
            </div>
        </div>

        <div class="column">
            <div class="box has-text-centered">
                <label class="label">SPEED</label>
                <span class="is-size-2 has-text-success">{{ $port_stats->last()->port_speed }} Mbit/s</span>

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

        <div class="column is-12">
            <div class="box">
                <h2 class="subtitle">Utilization RX/TX</h2>

                <label class="label">RX: {{ $utilization_rx }}%</label>
                <progress class="progress is-primary" value="{{ $utilization_rx }}"
                    max="{{ $speed }}">{{ $utilization_rx }}</progress>
                <label class="label">TX: {{ $utilization_tx }}%</label>
                <progress class="progress is-link" value="{{ $utilization_tx }}"
                    max="{{ $speed }}">{{ $utilization_tx }}</progress>
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
