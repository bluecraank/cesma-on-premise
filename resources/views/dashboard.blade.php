@section('title', 'Test')


<x-layouts.main>
    <nav class="level is-mobile">
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">{{ __('Switches online') }}</p>
                <p class="title">{{ $devicesOnline[0] }} of {{ $devicesOnline[1] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">{{ __('Vlans') }}</p>
                <p class="title">{{ $vlans }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Clients</p>
                <p class="title">{{ $clients }}</p>
            </div>
        </div>
    </nav>

    <div class="columns">
        <div class="column is-3">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-ethernet"></i></span>
                        {{ __('Ports to vlans') }}
                    </p>

                </header>

                <div class="card-content">
                    @include('charts.portsToVlans')
                </div>
            </div>
        </div>

        <div class="column is-3">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-ethernet"></i></span>
                        {{ __('Ports online') }}
                    </p>

                </header>

                <div class="card-content">
                    @include('charts.portsOnline')
                </div>
            </div>
        </div>

        <div class="column is-3">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-desktop-classic"></i></span>
                        {{ __('Clients to vlans') }}
                    </p>

                </header>

                <div class="card-content">
                    @include('charts.clientsToVlans')
                </div>
            </div>
        </div>

        <div class="column is-3">
            {{-- <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-desktop-classic"></i></span>
                        {{ __('Clients to vlans') }}
                    </p>

                </header>

                <div class="card-content">
                    @include('charts.clientsToVlans')
                </div>
            </div> --}}
        </div>
    </div>

    <div class="columns">
        <div class="column is-12">
            <livewire:show-notifications lazy />
        </div>
    </div>
</x-layouts>
