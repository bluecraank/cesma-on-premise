<x-layouts.main>
    <div class="columns ml-1 mr-3">
        <div class="column">
            <div class="columns">
                <div class="column is-6">
                    <h1 class="title">
                        {{ $device->name }}
                    </h1>

                    <h1 class="subtitle">
                        Portstatistiken

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
                <p class="subtitle"></p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>{{ __('Switch.Live.VlanSummary') }}</strong></p>
                <p class="subtitle"></p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>{{ __('Switch.Live.TrunkSummary') }}</strong></p>
                <p class="subtitle"></p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading"><strong>Ports UP</strong></p>
                <p class="subtitle"></p>
            </div>
        </div>
    </div>


    <div class="column is-12">
        <div class="box">
            <h2 class="subtitle">Ports
            </h2>

            <table class="table is-striped is-narrow is-fullwidth">
                <thead>
                    <tr>
                        <th>Port</th>
                        <th>Activity</th>
                        <th>Errors TX</th>
                        <th>Errors RX</th>
                        <th>Utilization</th>
                    </tr>
                </thead>

                <tbody class="live-body">
                    @foreach ($ports as $port)
                        <tr>

                            {{-- <td>{{ $port->port_name }}</td>
                            <td>{{ $port->port_activity }}</td>
                            <td>{{ $port->port_errors_tx }}</td>
                            <td>{{ $port->port_errors_rx }}</td>
                            <td>{{ $port->port_utilization }}</td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    </div>

    </x-layouts>
