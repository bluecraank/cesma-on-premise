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
                <th>Gebäude</th>
                <th>Standort</th>
                <th style="width:150px;text-align:center">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($buildings as $building)
            <tr>
                <td>{{ $building->name }}</td>
                <td>{{ $locations[$building->location_id]->name }}</td>
                <td style="width:150px;">
                    <div class="has-text-centered">
                        <button onclick="editBuildingModal('{{ $building->id }}', '{{ $building->name }}')" class="button is-info is-small"><i class="fa fa-gear"></i></button>
                        <button onclick="deleteBuildingModal('{{ $building->id }}', '{{ $building->name }}')" class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                    </div>
                </td>
            </tr>
            @endforeach
    </table>
</div>