<div class="modal clickable-tags modal-vlan-bulk-edit">
    <form onchange="checkVlanCount()" id="bulk-edit-ports" action="/switch/{{ $device->id }}/action/bulk-update-ports" method="post">
        @csrf
        <input type="hidden" class="ports" value='' name="ports">
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Edit.Bulk.Ports') }}</p>
                <span class="tag">NOT SELECTED</span>
                <span class="tag is-primary">SELECTED</span>
            </header>
            <section class="modal-card-body">
                <input type="hidden" name="device_id" class="device_id" value="{{ $device->id }}">

                <label class="label is-small">Verfügbare Ports</label>
                <div class="ports">
                    @php
                        $ports = $ports->sortBy('name')->keyBy('id')->toArray();
                    @endphp
                    @foreach ($ports as $key => $port)
                        @if (!str_contains($port['name'], 'Trk'))
                            <span title="{{ $port['name'] }}" class="is-clickable tag"
                                data-id="{{ $port['name'] }}">{{ $port['name'] }}</span>
                        @endif
                    @endforeach
                </div>

                <br><br>
                <div class="vlans">

                    <select multiple="multiple" id="vlan-select-ms" name="vlans_selected[]">
                        @php
                            $vlans = $vlans
                                ->sortBy('vlan_id')
                                ->keyBy('id')
                                ->toArray();
                        @endphp
                        @foreach ($vlans as $vlan)
                            <option value="{{ $vlan['id'] }}">{{ $vlan['name'] }}</option>
                        @endforeach
                    </select>

                </div>
                <br><br>
                <label class="label is-small">Setze VLANs als...</label>
                <div class="columns">
                    <div class="column is-6">
                        <input type="radio" value="untagged" name="type"> Untagged / Native

                    </div>
                    <div class="column is-6">
                        <input type="radio" value="tagged" name="type"> Tagged / Allowed

                    </div>
                </div>

                <div class="is-untagged-message is-hidden">
                    <div class="notification is-danger">
                        Es wurde mehr als ein VLAN ausgewählt, obwohl nur ein VLAN als untagged / native gesetzt werden kann.
                      </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                @if (Auth::user()->role >= 1)
                    <button onclick="event.preventDefault();submitBulkEditPorts(this, '{{ $device->id }}');" disabled type="submit" class="button is-submit is-primary">{{ __('Button.Save') }}</button>
                @endif
                <button onclick="$('.modal-vlan-bulk-edit').hide();return false;" type="button"
                    class="is-cancel button">{{ __('Button.Close') }}</button>
                <span class="is-info submit-wait is-hidden">{{ __('Msg.SubmitWait') }}</span>
            </footer>
        </div>

    </form>
</div>
