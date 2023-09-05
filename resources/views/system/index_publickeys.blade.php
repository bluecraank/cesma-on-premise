@section('title', __('SSH Publickeys'))

<x-layouts.main>
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-key"></i></span>
                {{ __('SSH Publickeys') }}
            </p>

            <div class="mr-5 in-card-header-actions">
                <div class="is-inline-block ml-2">
                    @if (Auth::user()->role >= 1)
                        <button data-modal="create-public-key" class="button is-small is-success"><i
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
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Key') }}</th>
                                <th>{{ __('Added') }}</th>
                                <th class="has-text-centered">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($publickeys->count() == 0)
                                <tr>
                                    <td colspan="4" class="has-text-centered">
                                        {{ __('Add public keys to sync them across devices.') }}
                                    </td>
                                </tr>
                            @endif
                            @foreach ($publickeys as $publickey)
                                <tr>
                                    <td>{{ $publickey->description }}</td>
                                    <td>{{ mb_substr(Crypt::decrypt($publickey->key), 0, 77) }}...</td>
                                    <td>{{ $publickey->created_at }}</td>
                                    <td class="is-actions-cell has-text-centered">
                                        @if(Auth::user()->role >= 1)
                                        <div class="buttons is-small is-right">
                                            <button data-description="{{ $publickey->description }}" data-id="{{ $publickey->id }}" data-modal="delete-public-key" class="button is-small is-danger" type="button">
                                                <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                            </button>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($publickeys->count() != 0)
                <p class="has-text-centered">
                    {{ __('Add public keys to sync them across devices.') }}
                </p>
            @endif
        </div>
    </div>

    @if ($publickeys->count() <= 2)
        <div class="notification is-danger">
            <p>{{ __('Syncing is disabled to prevent misconfiguration because only 2 public keys are added.') }}</p>
        </div>
    @endif
    <div class="notification is-info">
        <p>{{ __('Go to') }} <a href="{{ route('devices') }}">{{ __('Devices') }}</a> {{ __('to sync added public keys on aruba switches') }}</p>
    </div>

    @include('modals.publickeys.create')
    @include('modals.publickeys.delete')
    </x-layouts>
