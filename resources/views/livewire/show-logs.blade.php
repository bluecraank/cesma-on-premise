@section('title', 'Logs')

<div>
    <div class="modal modal-log-detail">
        <div class="modal-background"></div>

        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Details of log') }}</p>
            </header>
            <section class="modal-card-body">
                <pre class="content">

                </pre>
            </section>
            <footer class="modal-card-foot">
                <button onclick="this.closest('.modal').style.display = 'none'" type="button"
                    class="is-cancel button no-prevent">{{ __('Close') }}</button>
            </footer>
        </div>
    </div>
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-math-log"></i></span>
                {{ __('Logs') }}
            </p>

            <div class="mr-5 in-card-header-actions">

                <x-export-button :filename="__('Logs')" table="table" />

                <div class="is-inline-block">
                    <div class="select is-small">
                        <select wire:model.live="level">
                            <option value="">{{ __('All levels') }}</option>
                            <option value="error">ERROR</option>
                            <option value="warning">WARNING</option>
                            <option value="info">INFO</option>
                            <option value="debug">DEBUG</option>
                        </select>
                    </div>
                </div>

                <div class="is-inline-block">
                    <div class="select is-small">
                        <select wire:model.live="category">
                            <option value="">{{ __('All categories') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="is-inline-block">
                    <div class="field">
                        <div class="control has-icons-right">
                            <input class="input is-small" type="text" wire:model.live.debounce.400ms="search"
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
                                <th style="width:200px;" >{{ __('Time') }}</th>
                                <th>{{ __('User') }}</th>
                                <th style="width:100px;">{{ __('Level') }}</th>
                                <th style="width:150px;">{{ __('Category') }}</th>
                                <th style="width:150px">{{ __('Device') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($logs->count() == 0)
                                <tr>
                                    <td colspan="7" class="has-text-centered">{{ __('No logs found') }}</td>
                                </tr>
                            @endif
                            @foreach ($logs as $log)
                                @php
                                    // Readable details: pretty printed json and where the action came from
                                    $info = json_decode($log->additional_info ?? '', true);
                                    $details = is_array($info) ? json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : ($log->additional_info ?? '');
                                    if ($log->ip) {
                                        $details = trim($details . "\n\nIP: " . $log->ip);
                                    }
                                @endphp
                                <tr wire:key="log-{{ $log->id }}">
                                    <td>{{ $log->created_at->format('d.m.Y H:i:s') }}</td>
                                    <td>{{ $log->user ?? 'No User' }}</td>
                                    <td style="width:100px;"><span
                                            class="log-{{ strtolower($log->level) }}">{{ strtoupper($log->level) }}</span>
                                    </td>
                                    <td>
                                        {{ $log->category }}
                                    </td>
                                    <td>
                                        {{ $log->device_name }}
                                    </td>
                                    <td>{{ $log->description }}</td>
                                    <td class="is-pulled-right">
                                        @if ($details !== '')
                                            <button data-text="{{ $details }}" class="show-details-of-log is-info button is-small no-prevent">{{ __('Details') }}</button>
                                        @endif
                                    </td>
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
            color: #868eeb;
        }

        .log-warning {
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
