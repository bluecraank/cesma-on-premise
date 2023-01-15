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

<div class="modal modal-delete-backup">
    <form action="/switch/backup/delete" method="post">
        <input type="hidden" name="_method" value="delete" />
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Backup.Delete.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Backup-ID</label>
                    <div class="control">
                        <input class="backup-id input" type="text" disabled value="">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Backup.Created') }}</label>
                    <div class="control">
                        <input class="input backup-date" type="text" disabled value="">
                        <input class="input backup-date" name="date" type="hidden" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-danger">{{ __('Button.Delete') }}</button>
                <button onclick="$('.modal-delete-backup').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>

<div class="modal modal-upload-backup">
    <form action="/switch/backup/restore" method="post">
        <input class="id input" name="id" type="hidden" value="">
        <input class="device-id input" name="device-id" type="hidden" value="">

        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Backup.Restore.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Switch</label>
                    <div class="control">
                        <input class="name input" required type="text" name="switch" disabled value="">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Backup.Created') }}</label>
                    <div class="control">
                        <input class="input created" required type="text" name="date" disabled value="">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Backup.Restore.YourPassword') }}</label>
                    <div class="control">
                        <input class="input" type="password" name="password" required placeholder="Dein Passwort">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Backup.Restore.SwitchPassword') }}</label>
                    <div class="control">
                        <input class="input" type="password" name="password-switch" required placeholder="Dein Passwort">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-warning">{{ __('Backup.Restore') }}</button>
                <button onclick="$('.modal-upload-backup').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
</x-layouts>
