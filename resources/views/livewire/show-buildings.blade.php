@section('title', __('Buildings'))

<div>
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-office-building"></i></span>
                {{ __('Buildings') }}
            </p>

            <div class="mr-5 in-card-header-actions">
                <div class="is-inline-block ml-2">
                    @if (Auth::user()->role >= 1)
                        <button data-modal="create-building" class="button is-small is-success"><i
                                class="mdi mdi-plus mr-1"></i>
                            {{ __('Create') }}</button>
                    @endif
                </div>

                <x-export-button :filename="__('Buildings')" table="table" />

                <div class="is-inline-block">
                    <div class="field">
                        <div class="control has-icons-right">
                            <input class="input is-small" type="text" wire:model="searchTerm"
                                placeholder="{{ __('Search for buildings') }}">
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
                                <th>{{ __('Site') }}</th>
                                <th>Switches</th>
                                <th class="has-text-centered">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($buildings as $building)
                                <tr>
                                    <td>{{ $building->name }}</td>
                                    <td>{{ $building->site->name }}</td>
                                    <td>{{ $building->devices()->count() }}</td>
                                    <td class="is-actions-cell has-text-centered">
                                        <div class="buttons is-right">
                                            <button data-modal="update-building"
                                                wire:click="show({{ $building->id }}, 'update')"
                                                class="button is-small is-primary" type="button">
                                                <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                            </button>
                                            <button data-modal="delete-building"
                                                wire:click="show({{ $building->id }}, 'delete')"
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
                {{ $buildings->links('pagination::default') }}
            </div>
        </div>
    </div>

    <div>
        @if (Auth::user()->role >= 1)
            @include('modals.building.create')
            @livewire('building-modals')
        @endif
    </div>
</div>
