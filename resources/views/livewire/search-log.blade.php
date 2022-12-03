<div class="box">
    <h1 class="title is-pulled-left">Logs</h1>

    <div class="is-pulled-right ml-4">
        <button onclick="$('.modal-add-site').show();return false;" class="button is-success">Create</button>
    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input" type="text" wire:model.debounce.500ms="searchTerm" placeholder="Search a location...">
                <span class="icon is-small is-right">
                    <i class="fas fa-search fa-xs"></i>
                </span>
            </div>
        </div>
    </div>

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>User</th>
                <th style="width:150px;text-align:center">Aktion</th>
                <th>RAW Daten</th>
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
            </tr>
            @endforeach
    </table>
</div>