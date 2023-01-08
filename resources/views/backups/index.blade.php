<x-layouts.main>
<div class="box">
    <h1 class="title is-pulled-left">Backups</h1>

    <div class="is-pulled-right ml-4">

    </div>

    <div class="is-pulled-right">

    </div>

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>Switch</th>
                <th>Letztes Backup</th>
                <th>Status</th>
                <th style="width:150px;text-align:center">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($devices as $device)
            @php
                if(isset($device->last_backup->status) and $device->last_backup->status == 1) {
                    $status = 'Erfolgreich';
                    $num_status = 1;
                } else {
                    $status = 'Fehlgeschlagen';
                    $num_status = 0;
                }
            @endphp
            <tr>
                <td>{{ $device->name }}</td>
                <td>{{ isset($device->last_backup->created_at) ? $device->last_backup->created_at->diffForHumans() : "N/A" }}</td>
                <td>{{ $status }}</td>
                <td style="width:250px;">
                    <div class="has-text-centered">
                        <a title="Ansehen" class="button is-success is-small" href="/switch/{{ $device['id'] }}/backups">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        <a title="Herunterladen" class="button is-small is-primary" @php if($num_status) { echo 'href="/switch/download/backup/'.$device->last_backup->id.'"'; } else { echo 'disabled'; } @endphp download="backup.txt">
                            <i class="fa fa-download"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
    </table>
</div>


</x-layouts>
