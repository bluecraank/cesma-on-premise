<div class="box">
    <h1 class="title is-pulled-left">{{ __('Header.Locations') }}</h1>

    <div class="is-pulled-right ml-4">
        @if (Auth::user()->role == 'admin')
            <button onclick="$('.modal-add-site').show();return false;" class="is-small button is-success"><i
                    class="fas fa-plus"></i></button>
        @endif
    </div>

    <div class="is-pulled-right">
        <div class="field">
            <div class="control has-icons-right">
                <input class="input is-small" type="text" wire:model.debounce.500ms="searchTerm"
                    placeholder="{{ __('Search.Placeh.Location') }}">
                <span class="icon is-small is-right">
                    <i class="fas fa-search fa-xs"></i>
                </span>
            </div>
        </div>
    </div>

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>{{ __('Buildings') }}</th>
                <th>{{ __('Location') }}</th>
                <th style="width:150px;text-align:center">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($buildings as $building)
                <tr>
                    <td>{{ $building->name }}</td>
                    <td>{{ $locations[$building->location_id]->name }}</td>
                    <td style="width:150px;">
                        <div class="field has-addons is-justify-content-center">

                            @if (Auth::user()->role == 'admin')
                                <div class="control">
                                    <button onclick="editBuildingModal('{{ $building->id }}', '{{ $building->name }}')"
                                        class="button is-info is-small"><i class="fa fa-gear"></i></button>
                                </div>
                                <div class="control">
                                    <button
                                        onclick="deleteBuildingModal('{{ $building->id }}', '{{ $building->name }}')"
                                        class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
    </table>
</div>
