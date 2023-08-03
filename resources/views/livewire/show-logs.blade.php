@inject('DiffClass', 'App\Helper\Diff')
@section('title', 'Logs')

<div class="box">
    <h1 class="title is-pulled-left">{{ __('Log') }}</h1>

    <div class="is-pulled-right ml-4">

    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <div class="select">
                    <select wire:model.debounce.500ms="topic" name="topic" id="">
                        <option value="">Select context</option>
                        @foreach($topics as $topic)
                            <option value="{{ $topic->category }}">{{ $topic->category }}</option>
                        @endforeach
                    </select>
                </div>
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
                <th>Zustand</th>
                <th>Switch</th>
                <th>{{ __('Log.Action') }}</th>
                <th style="width:250px;">Informationen</th>
                <th>{{ __('Log.User') }}</th>
                <th>Kategorie</th>
                <th style="width:200px;" class="has-text-centered">Zeitpunkt</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td><span class="log-{{ strtolower($log->level) }}">{{ strtoupper($log->level) }}</span></td>
                    <td>
                        {{ $log->device_name }}
                    </td>
                    <td style="width:450px;">{{ $log->description }}</td>
                    <td style="width:250px;">
                        {{ $log->additional_info }}
                    </td>
                    <td>{{ $log->user ?? "No User" }}</td>
                    <td>
                        {{ $log->category }}
                    </td>
                    <td class="has-text-centered">{{ $log->created_at->format('m/d/Y H:i:s') }}</td>

                </tr>
            @endforeach
    </table>
    <div>
        {{ $logs->links('pagination::default') }}
    </div>
</div>