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
        <div class="column is-3">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-ethernet"></i></span>
                        {{ __('Ports to untagged vlans') }}
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
                @php
                    $time = Illuminate\Support\Carbon::parse(File::get(storage_path('logs/worker.log')));
                @endphp
                @if($time->diffInMinutes() > 5)
                    <div class="notification is-danger">{{ __('Check service! Last service run: ').$time->diffForHumans() }}</div>
                @endif
        </div>
    </div>


    <div class="columns">
        <div class="column is-6">
            <div class="card has-table">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-bell"></i></span>
                        {{ __('Notifications') }}
                    </p>

                </header>

                <div class="card-content">
                    <div class="b-table has-pagination">
                        <div class="table-wrapper has-mobile-cards" style="max-height:600px;">
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
                                    @foreach ($notifications->where('type', '!=', 'uplink') as $notification)
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

        <div class="column is-6">
            <div class="card has-table">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-sync"></i></span>
                        {{ __('Vlan sync status') }}
                    </p>

                </header>

                <div class="card-content">
                    <div class="b-table has-pagination">
                        <div class="table-wrapper has-mobile-cards" style="max-height:600px;">
                            <table class="is-fullwidth is-striped is-hoverable is-narrow is-fullwidth table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Device') }}</th>
                                        <th>{{ __('Vlans exist') }}</th>
                                        <th>{{ __('Vlan naming') }}</th>
                                        <th>{{ __('Result') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($deviceStatus as $status)
                                        <tr>
                                            <td>{{ $status['name'] }}</td>
                                            <td>{{ $status['vlans'] }} of {{ $vlans }}</td>
                                            <td>{{ $status['correctNames'] }} of {{ $status['vlans'] }}</td>
                                            <td style="color:@if($status['vlans'] == $vlans && $status['correctNames'] == $status['vlans']) green; @else red; @endif">@if($status['vlans'] == $vlans && $status['correctNames'] == $status['vlans']) Fully synced @else Incomplete synced @endif</td>
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

    <div class="modal patchnotes">
        <form wire:submit="update">
            <div class="modal-background"></div>
            <div style="margin-top: 40px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Notification') }}</p>
                </header>
                <section class="modal-card-body">
                    <div class="is-size-3">Patchnotes</div>
                    <div class="is-size-4 ml-1">
                        v4.1
                    </div>
                    <div class="ml-4">
                        <label class="label">Fixed</label>
                        <ul style="list-style: square;" class="ml-5">
                            <li>Renaming multiple vlans did not work if one of the vlans did not exist</li>
                            <li>Fixed a bug where the log could not be written during the port vlan update, resulting in
                                a crash</li>
                            <li>Menu sorting updated</li>
                        </ul>
                        <label class="label mt-5">Features</label>
                        <ul style="list-style: square;" class="ml-5">
                            <li>New vlan sync status container on dashboard</li>
                        </ul>

                        <label class="label mt-5">Upcoming</label>
                        <ul style="list-style: square;" class="ml-5">
                            <li>Report generator</li>
                        </ul>
                    </div>

                </section>
                <footer class="modal-card-foot">
                    <button onclick="setCookie('patchnotes', '4.1', 365);" type="button"
                        class="button">{{ __('Close') }}</button>
                </footer>
            </div>
        </form>
    </div>

    <script>
        function setCookie(cname, cvalue, exdays) {
            const d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            let expires = "expires=" + d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";

            $(".modal.patchnotes").hide();
        }

        function getCookie(cname) {
            let name = cname + "=";
            let decodedCookie = decodeURIComponent(document.cookie);
            let ca = decodedCookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }

        let version = "4.1";
        let cookie = getCookie("patchnotes");
        if (cookie != version) {
            $(".patchnotes").show();
        }
    </script>
    </x-layouts>
