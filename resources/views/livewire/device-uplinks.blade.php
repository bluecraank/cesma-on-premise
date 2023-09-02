<div x-cloak class="card has-table" x-data="{ open: false }">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-arrow-up-down"></i></span>
            Uplinks
        </p>
        <a class="card-header-icon">
            <span class="icon" @click="open = !open"><i x-bind:class="!open ? 'mdi-chevron-up' : 'mdi-chevron-down'"
                    class="mdi"></i></span>
        </a>
    </header>
    <div class="card-content" x-show="open">
        <div class="b-table">
            <div class="table-wrapper has-mobile-cards">
                <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>{{ __('Members') }}</th>
                            @if (Auth::user()->role >= 1)
                                <th class="has-text-centered"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($uplinks as $port => $data)
                            <tr>
                                <td>{{ $port }}</td>
                                <td>{{ $data['members'] }}</td>
                                @if (Auth::user()->role >= 1)
                                    <td class="has-text-centered">
                                        <i wire:click="delete({{ $data['id'] }})"
                                            class="is-clickable mdi mdi-delete"></i></button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach

                        @if (count($uplinks) == 0)
                            )
                            <tr>
                                <td colspan="2" class="has-text-centered">{{ __('No uplinks found') }}
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <td>
                                {{-- <input type="text" wire:model="uplink" class="input" placeholder="Port"> --}}
                                {{ __('Add uplink') }}
                            </td>
                            <td>
                                <input type="text" wire:model.live="new_uplink" class="input is-small" placeholder="{{ __('Portnumber') }}">
                            </td>
                            <td class="has-text-centered">
                                <i wire:click="add()" class="p-1 is-clickable mdi mdi-plus"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
