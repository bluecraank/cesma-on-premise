<div x-cloak class="card has-table" x-data="{ open: true, editable: false }">
    <header class="card-header">
        <div class="card-header-title">
            <div title="{{ __('Expand table') }}" class="is-clickable has-text-link ml-2 mr-2" @click="fullwidth = !fullwidth">
                <span class="mdi" x-bind:class="fullwidth ? 'mdi-arrow-collapse-all' : 'mdi-arrow-expand-all'"></span>
            </div>
            <span class="icon"><i class="mdi mdi-ethernet"></i></span>
            {{ __('Portoverview') }} <button class="is-white is-loading button" style="display:none" wire:loading></button>
        </div>
        <a class="card-header-icon">

            @if (config('app.read-only')[$device->type])
                <span class="tag is-danger is-pulled-right">read-only</span>
            @endif

            @if (Auth::user()->role >= 1 && !config('app.read-only')[$device->type])
                <button x-cloak @if (!$device->active()) disabled @endif x-show="editable"  wire:click="save" @click="editable=false"
                    wire:loading.class="is-loading" class="is-save-button button is-small is-success is-pulled-right"><i
                        class="mdi mdi-content-save-check mr-2"></i> {{ __('Save') }}</button>

                <button x-cloak @if (!$device->active()) disabled @endif x-show="editable" wire:click="reset_ports"
                    @click="editable=false" class="is-cancel-button button is-small is-danger is-pulled-right ml-2"><i
                        class="mdi mdi-cancel mr-2"></i> {{ __('Cancel') }}</button>

                <button x-cloak @if (!$device->active()) disabled @endif x-show="!editable"
                    @click="editable=true" class="is-edit-button button is-small is-info is-pulled-right"><i
                        class="mdi mdi-pen mr-2"></i> {{ __('Edit') }}</button>
            @endif
        </a>
    </header>
    <div class="card-content" x-show="open">
        <div class="b-table">
            <div class="table-wrapper has-mobile-cards">
                <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th class="has-text-centered" style="width: 45px;">ifStatus</th>
                            <th class="has-text-centered" style="max-width: 120px;">ifSpeed</th>
                            <th class="has-text-centered" style="width: 60px;">ifIndex</th>
                            <th>{{ __('Description') }}</th>
                            <th>Untagged vlan</th>
                            <th>Tagged vlans</th>
                            <th class="has-text-left">{{ trans_choice('Clients', 2) }}</th>
                            {{-- <th class="has-text-centered">{{ __('Actions') }}</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ports as $port)
                            @if (!$port['memberOfTrunk'] && !str_contains($port['name'], "Management"))
                                <livewire:device-port :$port />
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
