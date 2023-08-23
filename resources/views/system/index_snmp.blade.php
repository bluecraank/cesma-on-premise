@section('title', __('SNMP'))

<x-layouts.main>
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-account"></i></span>
                {{ __('SNMP') }}
            </p>

            <div class="mr-5 in-card-header-actions">
                <div class="is-inline-block ml-2">
                    @if (Auth::user()->role >= 1)
                        <button data-modal="create-gateway" class="button is-small is-success"><i
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
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Hostname/IP') }}</th>
                                <th>{{ __('Connection') }}</th>
                                <th>{{ __('MAC table entries') }}</th>
                                <th class="has-text-centered">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($routers->count() == 0)
                                <tr>
                                    <td colspan="5" class="has-text-centered">
                                        {{ __('For better client discovery, please add all gateways of your network here.') }}
                                    </td>
                                </tr>
                            @endif
                            @foreach ($routers as $router)
                                <tr>
                                    <td>{{ $router->desc }}</td>
                                    <td>{{ $router->ip }}</td>
                                    <td>{!! $router->check ? '<span class="has-text-success">Successfully got SNMP data</span>' : '<span class="has-text-danger">No data retrieved from snmp</span>' !!}</td>
                                    <td>{{ $router->entries }}</td>
                                    <td class="is-actions-cell has-text-centered">
                                        <div class="buttons is-small is-right">
                                            <button data-id="{{ $router->id }}" data-ip="{{ $router->ip }}" data-modal="delete-gateway" class="button is-small is-danger" type="button">
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
            @if ($routers->count() != 0)
                <p class="has-text-centered">
                    {{ __('For better client discovery, please add all gateways of your network here.') }}
                </p>
            @endif
        </div>
    </div>

    <div class="notification is-info">
        <p>- {{ __('Clients are identified via the MAC table of the gateways added here') }}</p>
        <p>- {{ __('Ensure to configure Client discovery') }} - <a href="{{ route('vlans') }}">Go to Vlans</a></p>
    </div>

    @include('modals.router.create')
    @include('modals.router.delete')
    </x-layouts>
