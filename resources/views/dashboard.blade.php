@section('title', __('Dashboard'))


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
                <p class="heading">{{ __('Ports monitored') }}</p>
                <p class="title">{{ $ports }}</p>
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
        <div class="column is-2">
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

        <div class="column is-2">
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

        <div class="column is-2">
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


    </div>


    <div class="columns">
        <div class="column is-12">
            <div class="card has-table">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-bell"></i></span>
                        {{ __('Notifications') }}
                    </p>

                </header>

                <div class="card-content">
                    <div class="b-table has-pagination">
                        <div class="table-wrapper has-mobile-cards" style="overflow-y:scroll;max-height:600px;">
                            <table class="is-fullwidth is-striped is-hoverable is-narrow is-fullwidth table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Message') }}</th>
                                        <th>{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($notifications) == 0)
                                        <tr>
                                            <td colspan="4" class="p-3 has-text-centered">
                                                <span class="icon"><i class="mdi mdi-information-outline"></i></span>
                                                {{ __('No events') }}
                                            </td>
                                        </tr>
                                    @endif
                                    @foreach ($notifications as $notification)
                                        <tr>
                                            <td>{{ $notification->title }}</td>
                                            <td>{{ $notification->message }}</td>
                                            <td>{{ $notification->updated_at }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="columns">
        <div class="column is-12">
            <livewire:show-notifications lazy />
        </div>
    </div>
    </x-layouts>
