@if ($errors->any())
<div class="notification is-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session()->has('success'))
<div class="notification is-success">
    {{ session()->get('success') }}
</div>
@endif

<div class="box">
    <h1 class="title is-pulled-left">Standorte</h1>

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
                <th>Standort</th>
                <th>Gebäude</th>
                <th style="width:150px;text-align:center">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($locations as $location)
            <tr>
                <td>{{ $location->name }} ({{ $location->id-1 }})</td>
                <td>{{ $location->buildings }}</td>
                <td style="width:150px;">
                    <div class="has-text-centered">
                        <button onclick="editBuildingModal('{{ $location->id }}', '{{ $location->name }}')" class="button is-info is-small"><i class="fa fa-gear"></i></button>
                        <button onclick="deleteBuildingModal('{{ $location->id }}', '{{ $location->name }}')" class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                    </div>
                </td>
            </tr>
            @endforeach
    </table>
</div>