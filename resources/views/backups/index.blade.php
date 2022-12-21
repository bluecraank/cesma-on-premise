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
                <th>Erstellt</th>
                <th>Status</th>
                <th style="width:150px;text-align:center">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($backups as $backup)
            @php 
                if($backup->status == 1) {
                    $status = 'Erfolgreich';
                } else {
                    $status = 'Fehlgeschlagen';
                }
            @endphp
            <tr>
                <td>{{ $devices[$backup->device_id]->name }}</td>
                <td>{{ $backup->created_at }}</td>
                <td>{{ $status }}</td>
                <td style="width:250px;">
                    <div class="has-text-centered">
                        <a class="button is-success is-small" href="/switch/{{ $backup->device_id }}/backups">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        <a class="button is-small is-link" @php if($backup->status == 1) { echo 'href="/download/switch/backup/'.$backup->id.'"'; } else { echo 'disabled'; } @endphp download="backup.txt">
                            <i class="fa fa-download"></i>
                        </a>

                        <button onclick="" class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                    </div>
                </td>
            </tr>
            @endforeach
    </table>
</div>


</x-layouts>
