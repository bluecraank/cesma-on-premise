@section('title', __('Buildings'))

<div class="box">
    <h1 class="title is-pulled-left">{{ __('Buildings') }}</h1>

    <div class="is-pulled-right ml-4">
        @if (Auth::user()->role >= 1)
            <button data-modal="add-building" class="is-small button is-success"><i class="fas fa-plus mr-1"></i>
                {{ __('Button.Create') }}</button>
        @endif
    </div>

    <div class="is-pulled-right">
            <button title="Export zu CSV" class="button is-small is-primary export-csv-button mr-2" data-table="table" data-file-name="{{ __('Buildings') }}"><i class="fa-solid fa-file-arrow-down"></i></button>
    </div>

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>{{ __('Building') }}</th>
                <th>{{ __('Location') }}</th>
                <th style="width:150px;text-align:center">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @if (count($buildings) == 0)
                <tr>
                    <td colspan="3" style="text-align:center">{{ __('No buildings found') }}</td>
                </tr>
            @endif

            @foreach ($buildings as $building)
                <tr>
                    <td>{{ $building->name }}</td>
                    <td>{{ $building->site?->name }}</td>
                    <td style="width:150px;">
                        <div class="field has-addons is-justify-content-center">

                            @if (Auth::user()->role >= 1)
                                <div class="control">
                                    <button data-modal="edit-building" data-id="{{ $building->id }}"
                                        data-name="{{ $building->name }}" data-site_id="{{ $building->site_id }}"
                                        class="button is-info is-small"><i class="fa fa-gear"></i></button>
                                </div>
                                <div class="control">
                                    <button data-modal="delete-building" data-id="{{ $building->id }}"
                                        data-name="{{ $building->name }}" class="button is-danger is-small"><i
                                            class="fa fa-trash-can"></i></button>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
    </table>

    {{ $buildings->links('pagination::default') }}
</div>

@if (Auth::user()->role >= 1)
    @include('modals.create.CreateBuildingModal')
    @include('modals.edit.EditBuildingModal')
    @include('modals.delete.DeleteBuildingModal')
@endif
