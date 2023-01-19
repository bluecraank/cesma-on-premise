<x-layouts.main>
<div class="box">
    <h1 class="title is-pulled-left">{{ __('Header.Backups') }} {{ $device->name }}</h1>

    <div class="is-pulled-right ml-4">
    </div>

    <div class="is-pulled-right">
    </div>

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>{{ __('Backup.Created') }}</th>
                <th>Status</th>
                <th style="width:150px;text-align:center">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($backups as $backup)
            @php 
                if($backup->status == 1) {
                    $status = __('Backup.Success');
                } else {
                    $status = __('Backup.Failed');
                }
            @endphp
            <tr>
                <td>{{ $backup->created_at }}</td>
                <td>{{ $status }}</td>
                <td style="width:250px;">
                    <div class="has-text-centered">
                        <button title="{{ __('Backup.Restore') }}" onclick="restoreBackup('{{ $backup->id }}', '{{ $backup->created_at }}', '{{ $device->id }}', '{{ $device->name }}')" @php if($backup->status != 1) { echo 'disabled'; } @endphp class="button is-warning is-small"><i class="fa-solid fa-upload"></i></button>

                        <a title="{{ __('Backup.Download') }}" class="button is-small is-success" @php if($backup->status == 1) { echo 'href="/switch/backup/'.$backup->id.'/download"'; } else { echo 'disabled'; } @endphp download="backup.txt">
                            <i class="fa fa-download"></i>
                        </a>

                        <button title="{{ __('Backup.Delete') }}" onclick="deleteBackupModal('{{ $backup->id }}', '{{ $backup->created_at }}')" class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                    </div>
                </td>
            </tr>
            @endforeach
    </table>
</div>

@include('modals.SwitchDeleteBackupModal')
@include('modals.SwitchRestoreBackupModal')

</x-layouts>
