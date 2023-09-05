@section('title', 'Vlans')

<div>
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-web"></i></span>
                Vlans
            </p>

            <div class="mr-5 in-card-header-actions">
                <div class="is-inline-block ml-2">
                    @if (Auth::user()->role >= 1)
                        <button data-modal="create-vlan" class="button is-small is-success"><i
                                class="mdi mdi-plus mr-1"></i>
                            {{ __('Create') }}</button>
                    @endif
                </div>

                <x-export-button :filename="__('Vlans')" table="table" />

                <div class="is-inline-block">
                    <div class="field">
                        <div class="control has-icons-right">
                            <input class="input is-small" type="text" wire:model.live="search"
                                placeholder="{{ __('Search for vlans') }}">
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
                                <th>#</th>
                                <th>Name</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('IP-Network') }}</th>
                                <th class="has-text-centered">{{ __('Sync vlan') }}</th>
                                <th class="has-text-centered">{{ __('Has clients') }}</th>
                                <th class="has-text-centered">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($vlans->count() == 0)
                                <tr>
                                    <td colspan="8" class="has-text-centered is-size-4">
                                        {{ __('No vlan data found') }}</td>
                                </tr>
                            @endif

                            @foreach ($vlans as $vlan)
                                @php
                                    $sync = $clients = false;
                                    if ($vlan->is_synced == 1) {
                                        $sync = true;
                                    }
                                    if ($vlan->is_client_vlan == 1) {
                                        $clients = true;
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        {{ $vlan->vid }}
                                    </td>
                                    <td>
                                        {{ $vlan->name }}
                                    </td>
                                    <td>
                                        {{ $vlan->description }}
                                    </td>

                                    <td>
                                        {{ $vlan->ip_range }}
                                    </td>
                                    <td class="has-text-centered">
                                        <label class="switch is-rounded"><input
                                                @if (Auth::user()->role >= 1) wire:click="updateSlider({{ $vlan->id }}, 'sync', {{ $sync }})" @endif
                                                type="checkbox" value="false"
                                                @if ($sync) checked @endif>
                                            <span class="check"></span>
                                        </label>
                                        <span class="is-hidden">{{ $sync ? 'Ja' : 'Nein' }}</span>
                                    </td>
                                    <td class="has-text-centered">
                                        <label class="switch is-rounded"><input
                                                @if (Auth::user()->role >= 1) wire:click="updateSlider({{ $vlan->id }}, 'clients', {{ $clients }})" @endif
                                                type="checkbox" value="false"
                                                @if ($clients) checked @endif>
                                            <span class="check"></span>
                                        </label>
                                        <span class="is-hidden">{{ $clients ? 'Ja' : 'Nein' }}</span>
                                    </td>
                                    <td class="is-actions-cell has-text-centered">
                                        <div class="field has-addons is-justify-content-center">
                                            <div class="control">
                                                <a class="button is-success is-small"
                                                    href="{{ route('show-vlan', $vlan->id) }}">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            </div>
                                            @if (Auth::user()->role >= 1)
                                                <div class="control">
                                                    <button data-modal="update-vlan"
                                                        wire:click="show({{ $vlan->id }}, 'update')"
                                                        class="button is-info is-small"><i
                                                            class="mdi mdi-pencil"></i></button>
                                                </div>
                                                <div class="control">
                                                    <button data-modal="delete-vlan"
                                                        wire:click="show({{ $vlan->id }}, 'delete')"
                                                        class="button is-danger is-small"><i
                                                            class="mdi mdi-trash-can"></i></button>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $vlans->links('pagination::default') }}
            </div>
        </div>
    </div>


    <div>
        @if (Auth::user()->role >= 1)
            <section>
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <span class="icon"><i class="mdi mdi-web"></i></span>
                            {{ __('Actions for every switch') }}
                        </p>

                    </header>

                    <div class="card-content">
                        <div class="buttons are-small">
                            @include('buttons.ButtonSyncVlan')
                        </div>
                    </div>
                </div>
            </section>

            @include('modals.vlan.create')
            @livewire('vlan-modals')
        @endif
    </div>
</div>
