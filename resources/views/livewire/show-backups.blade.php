@section('title', __('All Backups'))

<div>
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-switch"></i></span>
                Backups
            </p>

            <div class="mr-5 in-card-header-actions">
                <x-export-button :filename="__('Backups')" table="table" />

                <div class="is-inline-block">
                    <div class="field">
                        <div class="control has-icons-right">
                            <input class="input is-small" type="text" wire:model.live="search"
                                placeholder="{{ __('Search for switches') }}">
                            <span class="icon is-small is-right">
                                <i class="mdi mdi-search-web"></i>
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        <div class="card-content">
            <div class="b-table has-pagination">
                <div class="table-wrapper has-mobile-cards">
                    <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                        <thead>
                            <tr>
                                <th>Switch</th>
                                <th>{{ __('Date') }}</th>
                                <th>Status</th>
                                <th class="has-text-centered">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @empty($devices)
                                <tr>
                                    <td colspan="5" class="has-text-centered">{{ __('No Devices found.') }}</td>
                                </tr>
                            @endempty

                            @foreach ($devices as $device)
                                @php
                                    $num_status = 0;
                                    if (isset($device->last_backup->status) and $device->last_backup->status == 1) {
                                        $num_status = 1;
                                    } elseif (isset($device->last_backup->status) and $device->last_backup->status == 0) {
                                        $num_status = 0;
                                    } else {
                                        $num_status = 2;
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $device->name }}</td>
                                    <td>{{ isset($device->last_backup->created_at) ? $device->last_backup->created_at->diffForHumans() : 'N/A' }}
                                    </td>
                                    <td>
                                        @if ($num_status == 1)
                                            {{ __('Success') }}
                                        @elseif($num_status == 0)
                                            {{ __('Failed') }}
                                        @else
                                            {{ __('No backup made yet') }}
                                        @endif
                                    </td>
                                    <td class="is-actions-cell has-text-centered">
                                        <div class="field has-addons is-justify-content-center">
                                            <div class="control">
                                                <a title="{{ __('View this backup') }}"
                                                    class="button is-success is-small"
                                                    href="{{ route('show-device-backups', $device->id) }}">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            </div>
                                            <div class="control">
                                                <a title="{{ __('Download latest backup') }}"
                                                    class="button is-small is-primary"
                                                    @if ($num_status == 1) href="{{ route('download-backup', [$device->id, $device->last_backup->id]) }}" @else disabled @endif
                                                    download="backup.txt">
                                                    <i class="mdi mdi-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $devices->links('pagination::default') }}
            </div>
        </div>
    </div>

    <div>
        @if (Auth::user()->role >= 1)
            <section>
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <span class="icon"><i class="mdi mdi-switch"></i></span>
                            {{ __('Actions for every switch') }}
                        </p>
                    </header>

                    <div class="card-content">
                        <div class="buttons are-small">
                            @include('buttons.ButtonCreateBackup')
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>
</div>
