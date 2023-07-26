@section('title', __('Rooms'))

<x-layouts.main>
    <div class="box">
        <h1 class="title is-pulled-left">{{ __('Rooms') }}</h1>

        <div class="is-pulled-right ml-4">
            @if (Auth::user()->role >= 1)
                <button data-modal="add-room" class="is-small button is-success"><i
                        class="fas fa-plus mr-1"></i> {{ __('Button.Create') }}</button>
            @endif
        </div>

        <div class="is-pulled-right">

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
                @if (count($rooms) == 0)
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
                                        <button
                                            onclick="editRoomModal('{{ $room->id }}', '{{ $room->name }}', '{{ $room->building_id }}')"
                                            class="button is-info is-small"><i class="fa fa-gear"></i></button>
                                    </div>
                                    <div class="control">
                                        <button
                                            onclick="deleteRoomModal('{{ $room->id }}', '{{ $room->name }}')"
                                            class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
        </table>
    </div>



    @if (Auth::user()->role >= 1)
        @include('modals.create.CreateRoomModal')
        @include('modals.edit.EditRoomModal')
        @include('modals.delete.DeleteRoomModal')
    @endif
    </x-layouts>
