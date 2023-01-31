<div class="modal clickable-tags modal-vlan-tagging">
    <div class="modal-background"></div>

    <div style="margin-top: 40px" class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Tag vlans to Port <span class="port_id_title">A4</span></p>
            <span class="tag">NOT TAGGED</span>
            <span class="tag is-primary">TAGGED</span>
        </header>
        <section class="modal-card-body">
            <input type="hidden" name="device_id" class="device_id" value="{{ $device->id }}">

            <label class="label is-small">Ausgewählter Port</label>
            <input type="text" class="input is-small port_id" name="port_id" readonly="true">
            <br><br>

            <label class="label is-small">Verfügbare VLANs</label>
            @php
                $vlans = $vlans->sortBy('vlan_id')->keyBy('id')->toArray();
            @endphp
            @foreach ($vlans as $key => $vlan)
                <span title="{{ $vlan['name'] }}" class="is-clickable tag" data-id="{{ $key }}">{{ $vlan['vlan_id'] }}</span>
            @endforeach
        </section>
        <footer class="modal-card-foot">
            @if (Auth::user()->role >= 1)
            <button disabled onclick="updateTaggedVlans('{{ $device->id }}')"
                class="button is-submit is-primary">{{ __('Button.Save') }}</button>
            @endif
            <button onclick="$('.modal-vlan-tagging').hide();return false;" type="button"
                class="is-cancel button">{{ __('Button.Close') }}</button>
            <span class="is-info is-hidden">{{ __('Msg.SubmitWait') }}</span>
        </footer>
    </div>
</div>