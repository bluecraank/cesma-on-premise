@section('title', __('MAC Prefixes'))

<x-layouts.main>
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-ethernet"></i></span>
                {{ __('MAC Prefixes') }}
            </p>

            <div class="mr-5 in-card-header-actions">
                <div class="is-inline-block ml-2">
                    @if (Auth::user()->role >= 1)
                        <button data-modal="create-mac-type" class="button is-small is-success"><i
                                class="mdi mdi-plus mr-1"></i>
                            {{ __('Create') }}</button>
                    @endif
                </div>
            </div>
        </header>

        <div class="card-content p-3">
            <div class="b-table">
                <div class="table-wrapper has-mobile-cards">
                    <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                        <thead>
                            <tr>
                                <th>Prefix</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th class="has-text-centered">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($mac_types->count() == 0)
                                <tr>
                                    <td colspan="4" class="has-text-centered">
                                        {{ __('For better client categorizing, please add the most used mac prefixes here and assign them an icon.') }}
                                    </td>
                                </tr>
                            @endif
                            @foreach ($mac_types as $mac_type)
                                <tr>
                                    <td>{{ implode(":", str_split($mac_type->mac_prefix, 2)) }}</td>
                                    <td>{{ $mac_type->description}}</td>
                                    <td>{{ $mac_type->type }}</td>
                                    <td class="is-actions-cell has-text-centered">
                                        <div class="buttons is-small is-right">
                                            <button data-prefix="{{ $mac_type->mac_prefix }}" data-id="{{ $mac_type->id }}" data-type="{{ $mac_type->type }}" data-modal="delete-mac-type" class="button is-small is-danger" type="button">
                                                <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($mac_types->count() != 0)
                <p class="has-text-centered">
                    {{ __('For better client categorizing, please add the most used mac prefixes here and assign them an icon.') }}
                </p>
            @endif
        </div>
    </div>

    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-shape"></i></span>
                {{ __('MAC Prefix Icons') }}
            </p>
        </header>

        <div class="card-content p-3">
            <div class="b-table">
                <div class="table-wrapper has-mobile-cards">
                    <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                        <thead>
                            <tr>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Icon') }}</th>
                                <th class="has-text-centered">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($mac_type_icons->count() == 0)
                                <tr>
                                    <td colspan="4" class="has-text-centered">
                                        {{ __('For better client categorizing, please add the most used mac prefixes here and assign them an icon.') }}
                                    </td>
                                </tr>
                            @endif
                            @foreach ($mac_type_icons as $mac_type_icon)
                                <tr>
                                    <td>{{ $mac_type_icon->mac_type }}</td>
                                    <td>{{ $mac_type_icon->mac_icon }}</td>
                                    <td class="is-actions-cell has-text-centered">
                                        <div class="buttons is-small is-right">
                                            <button data-id="{{ $mac_type_icon->id }}" data-mac_icon="{{ $mac_type_icon->mac_icon }}" data-mac_type="{{ $mac_type_icon->mac_type }}" data-modal="update-mac-type-icon" class="button is-small is-info" type="button">
                                                <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($mac_type_icons->count() != 0)
                <p class="has-text-centered">
                    {{ __('For better client categorizing, please add the most used mac prefixes here and assign them an icon.') }}
                </p>
            @endif
        </div>
    </div>

    <div class="notification is-info">
        <p>{{ __('Discovered devices can be sorted and grouped by MAC prefixes') }}</p>
    </div>


    @include('modals.mac_types.create')
    @include('modals.mac_types.delete')
    @include('modals.mac_types.update-icon')
    </x-layouts>
