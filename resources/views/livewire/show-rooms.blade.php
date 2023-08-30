@section('title', __('Rooms'))

<div>
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-door"></i></span>
                {{ __('Rooms') }}
            </p>

            <div class="mr-5 in-card-header-actions">
                <div class="is-inline-block ml-2">
                    @if (Auth::user()->role >= 1)
                        <button data-modal="create-room" class="button is-small is-success"><i
                                class="mdi mdi-plus mr-1"></i>
                            {{ __('Create') }}</button>
                    @endif
                </div>

                <x-export-button :filename="__('Rooms')" table="table" />

                <div class="is-inline-block">
                    <div class="field">
                        <div class="control has-icons-right">
                            <input class="input is-small" type="text" wire:model.live="search"
                                placeholder="{{ __('Search for rooms') }}">
                            <span class="icon is-small is-right">
                                <i class="mdi mdi-search-web"></i>
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        <div class="card-content">
            <div class="b-table has-pagination">
                <div class="table-wrapper has-mobile-cards">
                    <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>{{ __('Building') }}</th>
                                <th>Switches</th>
                                <th class="has-text-centered">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rooms as $room)
                                <tr>
                                    <td>{{ $room->name }}</td>
                                    <td>{{ $room->building->name }}</td>
                                    <td>{{ $room->devices()->count() }}</td>
                                    <td class="is-actions-cell has-text-centered">
                                        <div class="buttons is-right">
                                            <button data-modal="update-room"
                                                wire:click="show({{ $room->id }}, 'update')"
                                                class="button is-small is-primary" type="button">
                                                <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                            </button>
                                            <button data-modal="delete-room"
                                                wire:click="show({{ $room->id }}, 'delete')"
                                                class="button is-small is-danger" type="button">
                                                <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $rooms->links('pagination::default') }}
            </div>
        </div>
    </div>

    <div>
        @if (Auth::user()->role >= 1)
            @include('modals.room.create')
            @livewire('room-modals')
        @endif
    </div>
</div>
