<div class="box">
    <h1 class="title is-pulled-left">{{ __('Header.Log') }}</h1>

    <div class="is-pulled-right ml-4">

    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input" type="text" wire:model.debounce.500ms="searchTerm"
                    placeholder="{{ __('Search.Placeh.Log') }}">
                <span class="icon is-small is-right">
                    <i class="fas fa-search fa-xs"></i>
                </span>
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

    <table class="table is-narrow is-fullwidth">
        <thead>
            <tr>
                <th>Level</th>
                <th>{{ __('Log.User') }}</th>
                <th class="has-text-centered">{{ __('Log.Action') }}</th>
                <th>Topic</th>
                <th style="width:200px;" class="has-text-centered">Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                
                @php
                    $context = $log->context['context'] ?? "";
                @endphp
                <tr>
                    <td><span class="log-{{ strtolower($log->level_name) }}">{{ $log->level_name }}</span></td>
                    <td>{{ $log->context['extra'] ?? "No User" }}</td>
                    <td style="width:450px;">{{ $log->message }}</td>
                    <td>
                        {{ $context }}
                    </td>
                    <td class="has-text-centered">{{ $log->created_at->format('m/d/Y H:i:s') }}</td>

                </tr>
            @endforeach
    </table>
</div>
