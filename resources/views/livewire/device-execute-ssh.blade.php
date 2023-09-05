<div>
    <div class="card command">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-bash"></i></span>
                {{ __('Execute SSH Command') }}
            </p>

            <div class="is-card-header-actions">
                <button class="button is-white is-collapsable-button">
                    <span class="icon"><i class="mdi mdi-chevron-down"></i></span>
                </button>
            </div>
        </header>
        <div class="card-content @if ($collapsed) is-hidden @endif">
            <div class="field">
                <label class="label">Site</label>
                <div class="field">
                    <div class="control has-icons-left">
                        <div class="select is-fullwidth">
                            <select disabled>
                                <option value="0" selected>{{ Auth::user()->currentSite()->name }}
                            </select>
                        </div>
                        <div class="icon is-small is-left">
                            <i class="mdi mdi-web"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="field mt-4 pt-4">
                <label class="label">Devices</label>
                <div class="field">
                    <div class="control has-icons-left">
                        <div class="select is-fullwidth" wire:loading.class="is-loading">
                            <select wire:change="updateType" wire:model="type" name="type">
                                <option value="0" selected>Select device group</option>
                                <option value="aos">AOS Devices</option>
                                <option value="aos-cx">AOS-CX Devices</option>
                                <option value="by-device">By Device</option>
                            </select>
                        </div>
                        <div class="icon is-small is-left">
                            <i class="mdi mdi-switch"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field @if ($type != 'by-device') is-hidden @endif">
                <label class="label">Select</label>
                <div class="field">

                    <div>
                        <select wire:model="devices" class="device-select" multiple="multiple">
                            @foreach ($devices as $device)
                                <option value="{{ $device['id'] }}">{{ $device['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <script>
                        document.addEventListener('update', function() {
                            setTimeout(() => {
                                $('select.device-select').multiSelect()
                            }, 50);
                        });
                        $('select.device-select').multiSelect()
                    </script>

                </div>
            </div>

            <div class="field mt-4 pt-4">
                <label class="label">Command</label>
                <div class="field">
                    <input class="input" wire:model="command" type="text">
                </div>
            </div>

            <div class="mt-4 pt-4">
                <button wire:click="execute()" class="button is-warning is-fullwidth"
                    @if ($type == '') disabled @endif>{{ __('Execute command') }}</button>
                <span class="help has-text-centered is-danger">
                    {{ __('Entered SSH commands are not checked and can cause problems if executed incorrectly') }}
                </span>
            </div>
        </div>
    </div>

    <div class="card results @if (!$collapsed) is-hidden @endif">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-bash"></i></span>
                {{ __('Results') }}
            </p>
        </header>
        <div class="card-content output-data">

        </div>
    </div>

</div>
