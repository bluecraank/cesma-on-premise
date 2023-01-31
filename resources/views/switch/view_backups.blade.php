<x-layouts.main>
    <div class="box">
        <h1 class="title is-pulled-left">{{ __('Header.Backup') }}</h1>

        <div class="is-pulled-right ml-4">

        </div>

        <div class="is-pulled-right">

        </div>

        <table class="table is-narrow is-hoverable is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>Switch</th>
                    <th>{{ __('Backup.Last') }}</th>
                    <th>Status</th>
                    <th style="width:150px;text-align:center">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $devices = $devices->sort(function ($a, $b) {
                        return strnatcmp($a['name'], $b['name']);
                    });
                @endphp
                @foreach ($devices as $device)
                    @php
                        if (isset($device->last_backup->status) and $device->last_backup->status == 1) {
                            $status = __('Backup.Success');
                            $num_status = 1;
                        } else {
                            $status = __('Backup.Failed');
                            $num_status = 0;
                        }
                    @endphp
                    <tr>
                        <td>{{ $device->name }}</td>
                        <td>{{ isset($device->last_backup->created_at) ? $device->last_backup->created_at->diffForHumans() : 'N/A' }}
                        </td>
                        <td>{{ $status }}</td>
                        <td style="width:150px;">
                            <div class="field has-addons is-justify-content-center">
                                <div class="control">
                                    <a title="{{ __('Backup.View') }}" class="button is-success is-small"
                                        href="/switch/{{ $device['id'] }}/backups">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                                <div class="control">
                                    <a title="Herunterladen" class="button is-small is-primary"
                                        @php if($num_status) { echo 'href="/switch/backup/'.$device->last_backup->id.'/download"'; } else { echo 'disabled'; } @endphp
                                        download="backup.txt">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
        </table>
    </div>

    <div class="box">
        <div class="label is-small">Alle Switche</div>
        <div class="buttons are-small">
            @include('buttons.ButtonCreateBackup')
        </div>
    </div>


    </x-layouts>
