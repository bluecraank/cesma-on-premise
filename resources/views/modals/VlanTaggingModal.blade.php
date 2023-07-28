<div class="modal clickable-tags modal-set-tagged-vlans">
    <div class="modal-background"></div>

    <div style="margin-top: 40px" class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Tagged/Allowed VLANs setzen</p>
        </header>
        <section class="modal-card-body">
            <input type="hidden" name="device_id" class="id" value="{{ $device->id }}">

            <label class="label is-small">Ausgewählter Port</label>
            <input type="text" class="input is-small name" name="port_id" readonly="true">
            <br><br>

            <label class="label is-small">Verfügbare VLANs</label>
            @php
                $vlans = $device->vlans
                    ->sortBy('vlan_id')
                    ->keyBy('id')
                    ->toArray();
            @endphp
            <div id="clickable-vlans">
                @foreach ($vlans as $key => $vlan)
                    <span title="{{ $vlan['name'] }}" class="is-clickable tag"
                        data-id="{{ $key }}">{{ $vlan['vlan_id'] }}</span>
                @endforeach
            </div>

            <br><br>

            <p>
                <label class="label is-small">Legende</label>
                <span class="tag is-info is-small">Allowed Untagged</span>
                <span class="tag is-primary is-small">Tagged</span>
                <span class="tag is-small">Nicht genutzt</span>
            </p>

            <div class="notification is-warning typ-warning is-hidden">
                Dieser Port ist zurzeit im Access-Mode.<br>
                Beim Speichern dieses Formulars wird der Port in den Trunk-Mode gesetzt.
            </div>
        </section>
        <footer class="modal-card-foot">
            @if (Auth::user()->role >= 1)
                <button disabled id="actionSetTaggedVlans"
                    type="button" class="button is-submit is-primary">{{ __('Button.Change') }}</button>
            @endif
            <button data-modal="set-tagged-vlans" type="button"
                class="is-cancel button">{{ __('Button.Close') }}</button>
            <span class="is-info is-hidden">{{ __('Msg.SubmitWait') }}</span>
        </footer>
    </div>
</div>
