<div class="modal modal-vlan-tagging">
    <div class="modal-background"></div>

    <div style="margin-top: 40px" class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Port A4 - VLAN Tagging</p>
            <span class="tag">NOT TAGGED</span>
            <span class="tag is-primary">TAGGED</span>
        </header>
        <section class="modal-card-body">
            <input type="hidden" name="device_id" class="device_id" value="{{ $device->id }}">

            <label class="label is-small">Ausgewählter Port</label>
            <input type="text" class="input is-small port_id" name="port_id" readonly="true">
            <br><br>

            <label class="label is-small">Verfügbare VLANs</label>
            @foreach ($vlans as $key => $vlan)
                <span class="is-clickable tag" data-id="{{ $key }}">{{ $key }}</span>
            @endforeach
        </section>
        <footer class="modal-card-foot">
            <button disabled onclick="updateTaggedVlans('{{ $device->id }}')"
                class="button is-submit is-primary">{{ __('Button.Save') }}</button>
            <button onclick="$('.modal-vlan-tagging').hide();return false;" type="button"
                class="is-cancel button">{{ __('Button.Cancel') }}</button>
            <span class="is-info is-hidden">Speichern...</span>
        </footer>
    </div>
</div>