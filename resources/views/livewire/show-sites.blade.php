@section('title', __('Sites'))

<div>
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-web"></i></span>
                {{ __('Sites') }}
            </p>

            <div class="mr-5 in-card-header-actions">
                <div class="is-inline-block ml-2">
                    @if (Auth::user()->role >= 1)
                        <button data-modal="create-site" class="button is-small is-success"><i
                                class="mdi mdi-plus mr-1"></i>
                            {{ __('Create') }}</button>
                    @endif
                </div>

                <x-export-button :filename="__('Sites')" table="table" />

                <div class="is-inline-block">
                    <div class="field">
                        <div class="control has-icons-right">
                            <input class="input is-small" type="text" wire:model.debounce.500ms="searchTerm"
                                placeholder="{{ __('Search for sites') }}">
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
                                <th>{{ __('Buildings') }}</th>
                                <th>Switches</th>
                                <th class="has-text-centered">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sites as $site)
                                <tr>
                                    <td>{{ $site->name }}</td>
                                    <td>{{ $site->buildings()->count() }}</td>
                                    <td>{{ $site->devices()->count() }}</td>
                                    <td class="is-actions-cell has-text-centered">
                                        <div class="buttons is-right">
                                            <button data-modal="update-site"
                                                wire:click="show({{ $site->id }}, 'update')"
                                                class="button is-small is-primary" type="button">
                                                <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                            </button>
                                            <button data-modal="delete-site"
                                                wire:click="show({{ $site->id }}, 'delete')"
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
                {{ $sites->links('pagination::default') }}
            </div>
        </div>
    </div>

    <div>
        @if (Auth::user()->role >= 1)
            @livewire('site-modals')
            @include('modals.site.create')
        @endif
    </div>
</div>
