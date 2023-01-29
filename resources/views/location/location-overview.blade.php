<x-layouts.main>
    <div class="box">
        <h1 class="title is-pulled-left">{{ __('Header.Locations') }}</h1>

        <div class="is-pulled-right ml-4">
            @if (Auth::user()->role == 'admin')
                <button onclick="$('.modal-add-site').show();return false;" class="is-small button is-success"><i
                        class="fas fa-plus mr-1"></i> {{ __('Button.Create') }}</button>
            @endif
        </div>

        <div class="is-pulled-right">

        </div>

        <table class="table is-narrow is-hoverable is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>{{ __('Location') }}</th>
                    <th style="width:150px;text-align:center">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($locations as $location)
                    <tr>
                        <td>{{ $location->name }}</td>
                        <td style="width:150px;">
                            <div class="field has-addons is-justify-content-center">

                                @if (Auth::user()->role == 'admin')
                                    <div class="control">
                                        <button
                                            onclick="editLocationModal('{{ $location->id }}', '{{ $location->name }}')"
                                            class="button is-info is-small"><i class="fa fa-gear"></i></button>
                                    </div>
                                    <div class="control">
                                        <button
                                            onclick="deleteLocationModal('{{ $location->id }}', '{{ $location->name }}')"
                                            class="button is-danger is-small"><i class="fa fa-trash-can"></i></button>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
        </table>
    </div>

    <div class="box">
        <h1 class="title is-pulled-left">{{ __('Header.Buildings') }}</h1>

        <div class="is-pulled-right ml-4">
            @if (Auth::user()->role == 'admin')
                <button onclick="$('.modal-add-building').show();return false;" class="is-small button is-success"><i
                        class="fas fa-plus mr-1"></i> {{ __('Button.Create') }}</button>
            @endif
        </div>

        <div class="is-pulled-right">

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
                @foreach ($buildings as $building)
                    <tr>
                        <td>{{ $building->name }}</td>
                        <td>{{ $building->getLocationName() }}</td>
                        <td style="width:150px;">
                            <div class="field has-addons is-justify-content-center">

                                @if (Auth::user()->role == 'admin')
                                    <div class="control">
                                        <button
                                            onclick="editBuildingModal('{{ $building->id }}', '{{ $building->name }}', '{{ $building->location_id }}')"
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


    <div class="box">
        <h1 class="title is-pulled-left">{{ __('Header.Rooms') }}</h1>

        <div class="is-pulled-right ml-4">
            @if (Auth::user()->role == 'admin')
                <button onclick="$('.modal-add-room').show();return false;" class="is-small button is-success"><i
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
                @foreach ($rooms as $room)
                    <tr>
                        <td>{{ $room->name }}</td>
                        <td>{{ $room->getBuildingName() }}</td>
                        <td style="width:150px;">
                            <div class="field has-addons is-justify-content-center">

                                @if (Auth::user()->role == 'admin')
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



    @if (Auth::user()->role == 'admin')
        @include('modals.create.LocationAddModal')
        @include('modals.create.BuildingAddModal')
        @include('modals.create.RoomAddModal')

        @include('modals.edit.LocationEditModal')
        @include('modals.edit.BuildingEditModal')
        @include('modals.edit.RoomEditModal')


        @include('modals.delete.LocationDeleteModal')
        @include('modals.delete.BuildingDeleteModal')
        @include('modals.delete.RoomDeleteModal')
    @endif
    </x-layouts>
