@section('title', 'Backups | ' . $device->name)

<div>
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-switch"></i></span>
                Backups
            </p>

            <div class="mr-5 in-card-header-actions">
                <x-export-button :filename="__('Backups')" table="table" />
            </div>
        </header>

        <div class="card-content">
            <div class="b-table has-pagination">
                <div class="table-wrapper has-mobile-cards">
                    <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                        <thead>
                            <tr>
                                <th>{{ __('Created') }}</th>
                                <th>Status</th>
                                <th class="has-text-centered">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @empty($backups)
                                <tr>
                                    <td colspan="5" class="has-text-centered">{{ __('No Backups found.') }}</td>
                                </tr>
                            @endempty

                            @foreach ($backups as $backup)
                                @php
                                    $num_status = 0;
                                    if ($backup->status == 1) {
                                        $num_status = 1;
                                    } elseif ($backup->status == 0) {
                                        $num_status = 0;
                                    } else {
                                        $num_status = 2;
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $backup->created_at }}</td>
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
                                        <div class="field has-addons">
                                            <div class="control">
                                                <button wire:click="download({{ $backup->id }})"
                                                    title="{{ __('Download') }}" class="button is-small is-success">
                                                    <i class="mdi mdi-download"></i>
                                                </button>
                                            </div>
                                            <div class="control">
                                                <button wire:click="show({{ $backup->id }}, 'delete')"
                                                    title="{{ __('Delete') }}" data-modal="delete-backup"
                                                    class="button is-danger is-small">
                                                    <i class="mdi mdi-trash-can"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $backups->links('pagination::default') }}
            </div>
        </div>
    </div>

    @if (Auth::user()->role >= 1)
        @livewire('device-backup-modals')
    @endif
</div>
