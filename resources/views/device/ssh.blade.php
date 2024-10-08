@section('title', 'SSH Execute')

<x-layouts.main>
    <div class="box">
        <div class="columns">
            <div class="column is-4">
                <h1 class="title">{{ __('SSH') }}</h1>
                <form action="" method="post" id="executeForm">
                    <input type="hidden" name="api_token" value="{{ Auth::user()->api_token }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div style="margin-top: 25px;">
                        <div class="mt-5 field">
                            <label class="label">{{ __('Select switch') }}</label>
                            <div class="select is-fullwidth is-small">
                                <select name="execute-specify-switch" id="">
                                    <option value="every-switch">{{ __('All Switches') }} - {{  Auth::user()->currentSite()->name }}</option>
                                    <option selected value="specific-switch">{{ __('Select switch') }} - {{  Auth::user()->currentSite()->name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-5 field">
                            <div class="execute-switch-select">
                                <select multiple="multiple" id="switch-select-ms" name="execute-switch-select">
                                    @foreach ($devices as $device)
                                        <option data-location="{{ $device->location }}" value="{{ $device->id }}">
                                            {{ $device->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="field">
                                <div class="location-select control has-icons-left is-hidden">
                                    <div class="select is-fullwidth is-small">
                                        <select required name="execute-switch-select-loc">
                                            @foreach ($sites as $site)
                                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="icon is-small is-left">
                                        <i class="fas fa-globe"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="which_switch" value="specific-switch">
                        <div clas="field">
                        </div>
                        <div class="mt-5 field">
                            <label class="label">{{ __('Command') }}</label>
                            <div class="control has-icons-left mb-2">
                                <div class="select is-small is-fullwidth">
                                    <select name="fast-command">
                                        <option selected>{{ __('Shortcuts') }}</option>
                                        <option value="vlan ID tagged PORT">Port an ein VLAN tagged setzen (vlan ID
                                            tagged PORT)</option>
                                        <option value="vlan ID untagged PORT">Port an ein VLAN untagged setzen (vlan ID
                                            untagged PORT)</option>
                                        <option value="vlan ID name NAME">VLAN umbennen (vlan ID name NAME)</option>
                                        <option value="show vlan ID">VLAN Infos auflisten (show vlan ID)</option>
                                        <option value="show interface PORT">Portinformationen anzeigen (show interface
                                            PORT)</option>
                                    </select>
                                </div>
                                <div class="icon is-small is-left">
                                    <i class="fas fa-repeat"></i>
                                </div>
                            </div>
                            <textarea required="true" spellcheck="false" required name="execute-command" class="textarea" placeholder="show vlan 1"></textarea>
                            <p class="help is-danger">{{ __('This action is unreversable') }}</p>
                        </div>

                        <button
                            onclick="$('.modal-confirmation-command').show();$('.confirmation-content').val($('textarea[name=\'execute-command\']').val());"
                            return false;" type="button" style="margin-top: 25px;"
                            class="button is-danger">{{ __('Execute command') }}</button>
                </form>

            </div>
        </div>

        <div class="column is-7 is-offset-1 ajax-results">
            <h1 class="title">Output</h1>
            <div>
                <div class="output-buttons buttons">

                </div>
                <div class="outputs">

                </div>
            </div>
        </div>

    </div>

    @include('modals.SwitchExecuteModal')

    </x-layouts>
