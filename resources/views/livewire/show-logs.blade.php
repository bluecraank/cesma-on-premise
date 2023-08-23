@section('title', 'Logs')

<div>
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-math-log"></i></span>
                {{ __('Logs') }}
            </p>

            <div class="mr-5 in-card-header-actions">

                <x-export-button :filename="__('Logs')" table="table" />

                <div class="is-inline-block">
                    <div class="field">
                        <div class="control has-icons-right">
                            <input class="input is-small" type="text" wire:model="searchTerm"
                                placeholder="{{ __('Search for logs') }}">
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
                                <th style="width:100px;">{{ __('Level') }}</th>
                                <th style="width:100px;">{{ __('Category') }}</th>
                                <th>{{ __('Action') }}</th>
                                <th style="width:250px;">{{ __('Details') }}</th>
                                <th class="is-pulled-right">{{ __('User') }}</th>
                                <th style="width:200px;" class="has-text-centered">{{ __('Time') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td style="width:100px;"><span
                                            class="log-{{ strtolower($log->level) }}">{{ strtoupper($log->level) }}</span>
                                    </td>
                                    <td>
                                        {{ $log->category }}
                                    </td>
                                    <td style="width:450px;">{{ $log->description }} @empty(!$log->device_name) {{ $log->device_name }} @endempty</td>
                                    <td style="width:250px;">
                                        {{ $log->additional_info }}
                                    </td>
                                    <td class="is-pulled-right">{{ $log->user ?? 'No User' }}</td>
                                    <td class="has-text-centered">{{ $log->created_at->format('m/d/Y H:i:s') }}</td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $logs->links('pagination::default') }}
            </div>
        </div>
    </div>

    <style>
        .log-debug {
            color: #d1bf84;
        }

        .log-info {
            color: #69c78f;
        }

        .log-error {
            color: #c63d3d;
        }
    </style>
</div>
