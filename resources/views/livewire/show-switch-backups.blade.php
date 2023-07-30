@section('title', 'All Backups for ' . $device->name)

{{-- <x-layouts.main> --}}
    <div class="box">
        <h1 class="title is-pulled-left">{{ __('Backups') }} - {{ $device->name }}</h1>

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
                        if ($backup->status == 1) {
                            $status = __('Backup.Success');
                        } else {
                            $status = __('Backup.Failed');
                        }
                    @endphp
                    <tr>
                        <td>{{ $backup->created_at }}</td>
                        <td>{{ $status }}</td>
                        <td style="width:250px;">
                            <div class="field has-addons is-justify-content-center">


                                @if (Auth::user()->role >= 1)
                                    <div class="control">
                                        <button disabled title="{{ __('Backup.Restore') }}" data-modal="restore-backup"
                                            data-id="{{ $backup->id }}" data-date="{{ $backup->created_at }}"
                                            data-device_id="{{ $device->id }}" data-device_name="{{ $device->name }}"
                                            @php if($backup->status != 1) { echo 'disabled'; } @endphp
                                            class="button is-warning is-small"><i class="fas fa-upload"></i></button>
                                    </div>
                                @endif
                                <div class="control">
                                    <a title="{{ __('Backup.Download') }}" class="button is-small is-success"
                                        @if($backup->status == 1) href="{{ route('download-backup', $backup->id) }}" @else disabled @endif
                                        download="backup.txt">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                                @if (Auth::user()->role >= 1)
                                    <div class="control">
                                        <button title="{{ __('Button.Delete') }}" data-modal="delete-backup"
                                            data-id="{{ $backup->id }}" data-date="{{ $backup->created_at }}"
                                            class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
        </table>
        
        {{ $backups->links('pagination::default') }}
    </div>

    @if (Auth::user()->role >= 1)
        @include('modals.delete.SwitchDeleteBackupModal')
        @include('modals.SwitchRestoreBackupModal')
    @endif
    {{-- </x-layouts> --}}