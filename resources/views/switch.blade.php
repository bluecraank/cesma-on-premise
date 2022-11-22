<x-layouts.main>
    <div class="box">
        <h1 class="title is-pulled-left">Dashboard</h1>

        <div class="is-pulled-right ml-4">
            <button class="button is-success">Create</button>
        </div>

        <div class="is-pulled-right">
            <div class="field">
                <div class="control has-icons-right">
                    <input class="input" type="text" placeholder="Find a device">
                    <span class="icon is-small is-right">
                        <i class="fas fa-search fa-xs"></i>
                    </span>
                </div>
            </div>
        </div>

        <table class="table is-narrow is-hoverable is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Hostname</th>
                    <th>Modell</th>
                    <th>Standort</th>
                    <th style="width:150px;text-align:center">Aktionen</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($devices as $device)
                    <tr>
                        <td>{{ $device->name }}</td>
                        <td>{{ $device->hostname }}</td>
                        <td>{{ $device->model }}</td>
                        <td>{{ $device->location_as_text }}</td>
                        <td style="width:150px;">
                            <div class="has-text-centered">
                                <a href="{{ $https }}://{{ $device->hostname }}" target="_blank">
                                    <button class="is-small button is-primary">
                                        <i class="fa fa-arrow-up-right-from-square"></i>
                                    </button>
                                </a>

                                <button onclick='editSwitchModal({{ $device->id }})'
                                    class="button is-info is-small"><i class="fa fa-gear"></i></button>
                                <button onclick='deleteSwitchModal({{ $device->id }})'
                                    class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                            </div>
                        </td>
                    </tr>
                @endforeach
        </table>
    </div>
</x-layouts>
