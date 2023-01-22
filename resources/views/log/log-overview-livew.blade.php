<div class="box">
    <h1 class="title is-pulled-left">{{ __('Header.Log') }}</h1>

    <div class="is-pulled-right ml-4">

    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input" type="text" wire:model.debounce.500ms="searchTerm" placeholder="{{ __('Search.Placeh.Log') }}">
                <span class="icon is-small is-right">
                    <i class="fas fa-search fa-xs"></i>
                </span>
            </div>
        </div>
    </div>

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>{{ __('Log.User') }}</th>
                <th style="width:150px;text-align:center">{{ __('Log.Action') }}</th>
                <th>{{ __('Log.Data') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('m/d/Y H:i:s') }}</td>
                <td>{{ $log->user }}</td>
                <td style="width:150px;">{{ $log->message }}</td>
                <td>
                    {{ $log->data }}
                </td>
            </tr>
            @endforeach
    </table>
</div>