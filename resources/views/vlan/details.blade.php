@section('title', $vlan['name'])

<x-layouts.main>
    <div>
        <div class="level">
            <div class="level-item has-text-centered">
                <div>
                    <p class="heading"><strong>Vorhanden auf</strong></p>
                    <p class="subtitle">{{ $has_vlan }}</p>
                </div>
            </div>
            <div class="level-item has-text-centered">
                <div>
                    <p class="heading"><strong>PORTS UNTAGGED</strong></p>
                    <p class="subtitle">{{ $count_untagged }}</p>
                </div>
            </div>
            <div class="level-item has-text-centered">
                <div>
                    <p class="heading"><strong>PORTS TAGGED</strong></p>
                    <p class="subtitle">{{ $count_tagged }}</p>
                </div>
            </div>
            <div class="level-item has-text-centered">
                <div>
                    <p class="heading"><strong>Ports online</strong></p>
                    <p class="subtitle">{{ $count_online }}</p>
                </div>
            </div>
        </div>

        <div class="card has-table">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-web"></i></span>
                    {{ __('Details of vlan ":vlan"', ['vlan' => $vlan['name']]) }}
                </p>

                <div class="mr-5 in-card-header-actions">
                    <div class="is-inline-block ml-2">
                        @if (Auth::user()->role >= 1)
                            <button data-modal="create-vlan" class="button is-small is-success"><i
                                    class="mdi mdi-plus mr-1"></i>
                                {{ __('Create') }}</button>
                        @endif
                    </div>

                    <x-export-button :filename="__('Ports of vlan :vlan', ['vlan'=>$vlan['name']])" table="table" />

                </div>
            </header>

            <div class="card-content">
                <div class="b-table has-pagination">
                    <div class="table-wrapper has-mobile-cards">
                        <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                            <thead>
                                <tr>
                                    <th>{{ __('Device') }}</th>
                                    <th>Untagged</th>
                                    <th>Tagged</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($devices as $switch => $p)
                                    <tr>
                                        <td style="width:200px;">{{ $switch }}</td>

                                        <td style="width:160px">
                                            @if (isset($untagged[$p->id]))
                                                {{ implode(', ', $untagged[$p->id]) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($untagged[$p->id]))
                                                {{ implode(', ', $tagged[$p->id]) }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
</x-layouts.main>
