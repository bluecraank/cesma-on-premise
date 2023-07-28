@section('title', __('Rooms'))

<div class="box">
    <h1 class="title is-pulled-left">{{ __('Rooms') }}</h1>

    <div class="is-pulled-right ml-4">
        @if (Auth::user()->role >= 1)
            <button data-modal="add-room" class="is-small button is-success"><i class="fas fa-plus mr-1"></i>
                {{ __('Button.Create') }}</button>
        @endif
    </div>

    <div class="is-pulled-right">
        <button title="Export zu CSV" class="button is-small is-primary export-csv-button mr-2" data-table="table" data-file-name="{{ __('Rooms') }}"><i class="fa-solid fa-file-arrow-down"></i></button>
    </div>

    <table class="table is-narrow is-hoverable is-striped is-fullwidth">
        <thead>
            <tr>
                <th>{{ __('Room') }}</th>
                <th>{{ __('Building') }}</th>
                <th style="width:150px;text-align:center">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @if ($rooms->count() == 0)
                <tr>
                    <td colspan="3" style="text-align:center">{{ __('No rooms found') }}</td>
                </tr>
            @endif

            @foreach ($rooms as $room)
                <tr>
                    <td>{{ $room->name }}</td>
                    <td>{{ $room->getBuildingName() }}</td>
                    <td style="width:150px;">
                        <div class="field has-addons is-justify-content-center">

                            @if (Auth::user()->role >= 1)
                                <div class="control">
                                    <button data-modal="edit-room" data-id="{{ $room->id }}"
                                        data-name="{{ $room->name }}" data-building_id="{{ $room->building_id }}"
                                        class="button is-info is-small"><i class="fa fa-gear"></i></button>
                                </div>
                                <div class="control">
                                    <button data-modal="delete-room" data-id="{{ $room->id }}"
                                        data-name="{{ $room->name }}" class="button is-danger is-small"><i
                                            class="fa fa-trash-can"></i></button>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
    </table>
    
    {{ $rooms->links('pagination::default') }}
</div>



@if (Auth::user()->role >= 1)
    @include('modals.create.CreateRoomModal')
    @include('modals.edit.EditRoomModal')
    @include('modals.delete.DeleteRoomModal')
@endif
