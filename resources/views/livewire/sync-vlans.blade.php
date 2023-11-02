<div>
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-information"></i></span>
                {{ __('Preparation') }}
            </p>
        </header>

        <div class="card-content p-3">
            @if (!$hidePreparation)
                <div class="columns">
                    <div class="column is-flex is-justify-content-center is-align-content-center">
                        <div class="field">

                            <label class="label">
                                {{ __('Select vlans to sync') }}
                            </label>
                            <div class="">
                                <select id="vlans" wire:model.live="selectedVlans" wire:change="updateVlans()"
                                    multiple="multiple">
                                    @foreach ($vlans as $vlan)
                                        <option value="{{ $vlan['vid'] }}">{{ $vlan['name'] }}</option>
                                    @endforeach
                                </select>
                                <a onclick="$('#vlans').multiSelect('select_all')">Select all</a> / <a
                                    onclick="$('#vlans').multiSelect('deselect_all')">Deselect all</a>
                            </div>
                        </div>
                    </div>

                    <div class="column is-flex is-justify-content-center is-align-content-center">
                        <div class="field">

                            <label class="label">
                                {{ __('Select devices to sync') }}
                            </label>
                            <div class="">
                                <select id="devices" wire:model.live="selectedDevices" wire
                                    wire:change="updateDevices()" multiple="multiple">
                                    @foreach ($devices as $device)
                                        <option value="{{ $device['id'] }}">{{ $device['name'] }}</option>
                                    @endforeach
                                </select>
                                <a onclick="$('#devices').multiSelect('select_all')">Select all</a> / <a
                                    onclick="$('#devices').multiSelect('deselect_all')">Deselect all</a>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <label class="label">
                            {{ __('Select actions to sync') }}
                        </label>
                        <div class="field mb-4 pb-4">

                            <div class="control">
                                <label class="b-checkbox checkbox"><input class="create-vlans is-checkbox" checked wire:model="createVlans"
                                        type="checkbox">
                                    <span class="check is-primary"></span>
                                    <span class="control-label">{{ __('Create vlans') }}</span>
                                </label>
                            </div>
                            <div class="control">
                                <label class="b-checkbox checkbox"><input class="rename-vlans is-checkbox" checked wire:model="renameVlans"
                                        type="checkbox">
                                    <span class="check is-primary"></span>
                                    <span class="control-label">{{ __('Rename vlans') }}</span>
                                </label>
                            </div>
                            <div class="control">
                                <label class="b-checkbox checkbox"><input class="tag-to-uplinks is-checkbox" wire:model="tagToUplinks" type="checkbox">
                                    <span class="check is-primary"></span>
                                    <span class="control-label">{{ __('Tag vlans to uplinks') }}</span>
                                </label>
                            </div>

                            <div class="control">
                                <label class="b-checkbox checkbox"><input class="delete-vlans is-checkbox" wire:model="deleteVlans" type="checkbox">
                                    <span class="check is-danger"></span>
                                    <span class="control-label">{{ __('Delete vlans') }}</span>
                                </label>
                            </div>

                        </div>
                        <div class="field is-flex pt-4 mt-4">
                            <button
                                class="is-start-button is-small submit no-prevent button is-primary">{{ __('Start syncing (Testmode)') }}</button>
                        </div>
                    </div>
                </div>

                <script>
                    $("select").multiSelect();

                    $(".is-start-button").on('click', function() {
                        if ($("#vlans").val() != null && $("#devices").val() != null && $("#vlans").val().length > 0 && $(
                                "#devices").val().length > 0) {
                            Livewire.dispatch('update', {
                                vlans: $("#vlans").val(),
                                devices: $("#devices").val(),
                                mode: false
                            })
                        } else {
                            $.notify("Select at least one device and one vlan", {
                                style: 'bulma-error',
                                autoHideDelay: 8000
                            });
                        }
                    });

                    $(".is-checkbox").on('click', function() {
                        if ($(".delete-vlans").is(':checked')) {
                            // False all checkboxes
                            $(".create-vlans").prop('checked', false);
                            $(".rename-vlans").prop('checked', false);
                            $(".tag-to-uplinks").prop('checked', false);

                            $.notify("You can't delete and create/rename/tag vlans at the same time", {
                                style: 'bulma-error',
                                autoHideDelay: 8000
                            });
                        }
                    });
                </script>
            @else
                <div class="p-4 has-text-centered">
                    <i class="mdi mdi-check is-size-1"></i>
                </div>
                <div class="p-4">
                    <button wire:click="start"
                        class="button is-small no-prevent is-warning">{{ __('Start sync now') }}</button>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-information"></i></span>
                {{ __('Results') }}
            </p>
        </header>

        <div class="card-content p-3">
            <div class="b-table">
                <div class="table-wrapper has-mobile-cards">
                    <table class="table is-fullwidth is-striped is-hoverable is-fullwidth">
                        <thead>
                            <tr>
                                <th>{{ __('Device') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Vlans created') }}</th>
                                <th>{{ __('Vlans renamed') }}</th>
                                <th>{{ __('Vlans deleted') }}</th>
                                <th>{{ __('Vlans tagged to uplinks') }}</th>
                            </tr>
                        </thead>
                        <tbody class="results">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
