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
                <th>Letztes Backup von</th>
                <th>Status</th>
                <th style="width:150px;text-align:center">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($devices as $device)
            @php
                if($device->last_backup->status == 1) {
                    $status = 'Erfolgreich';
                } else {
                    $status = 'Fehlgeschlagen';
                }
            @endphp
            <tr>
                <td>{{ $device->name }}</td>
                <td>{{ $device->last_backup->created_at }}</td>
                <td>{{ $status }}</td>
                <td style="width:250px;">
                    <div class="has-text-centered">
                        <a title="Ansehen" class="button is-success is-small" href="/switch/{{ $device['id'] }}/backups">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        <a title="Herunterladen" class="button is-small is-primary" @php if(1) { echo 'href="/download/switch/backup/'.$device['id'].'"'; } else { echo 'disabled'; } @endphp download="backup.txt">
                            <i class="fa fa-download"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
    </table>
</div>


</x-layouts>
