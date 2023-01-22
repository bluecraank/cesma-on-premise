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

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>{{ __('Log.User') }}</th>
                <th class="has-text-centered">{{ __('Log.Action') }}</th>
                <th class="has-text-centered">{{ __('Log.Data') }}</th>
                <th style="width:200px;" class="has-text-centered">Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td>{{ $log->user }}</td>
                    <td style="width:150px;">{{ $log->message }}</td>
                    <td>
                        {{ $log->data }}
                    </td>
                    <td class="has-text-centered">{{ $log->created_at->format('m/d/Y H:i:s') }}</td>

                </tr>
            @endforeach
    </table>
</div>
