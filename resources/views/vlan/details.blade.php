<x-layouts.main>
    <div style="display:none" class="notification status is-danger">
        <ul>
            <li></li>
        </ul>
    </div>
    <div class="box">
        <h1 class="title is-pulled-left">VLAN {{ $vlan_db['vid'] }} - {{ $vlan_db['name'] }}</span></h1>
        <br>
        <br>
        <br>

        <div class="level">
            <div class="level-item has-text-centered">
                <div>
                    <p class="heading"><strong>Vorhanden auf</strong></p>
                    <p class="subtitle">{{ $has_vlan }}</p>
                </div>
            </div>
            <div class="level-item has-text-centered">
                <div>
                    <p class="heading"><strong>PORTS UNTAGGED</strong></p>
                    <p class="subtitle">{{ $count_untagged }}</p>
                </div>
            </div>
            <div class="level-item has-text-centered">
                <div>
                    <p class="heading"><strong>PORTS TAGGED</strong></p>
                    <p class="subtitle">{{ $count_tagged }}</p>
                </div>
            </div>
            <div class="level-item has-text-centered">
                <div>
                    <p class="heading"><strong>Ports online</strong></p>
                    <p class="subtitle">{{ $count_online }}</p>
                </div>
            </div>
        </div>



        <div class="columns ml-1 mr-1">
            <div class="column is-12">
                {{-- <h2 class="subtitle">Switche</h2> --}}
                <table class="table is-striped is-narrow is-fullwidth">
                    <thead>
                        <tr>
                            <th>Switch</th>
                            <th>Untagged</th>
                            <th></th>
                            <th>Tagged</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ports as $switch => $port)
                            <tr>
                                <td>{{ $switch }}</td>

                                <td>
                                    @if (isset($port['untagged']))
                                        {{ implode(', ', $port['untagged']) }}
                                    @endif
                                </td>
                                <td style="width:100px;"></td>
                                <td>
                                    @if (isset($port['tagged']))
                                        {{ implode(', ', $port['tagged']) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        </x-layouts>
