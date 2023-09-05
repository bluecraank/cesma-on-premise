<div class="modal clickable-tags modal-set-tagged-vlans" @if($showModal) style="display: block" @endif>
    <div class="modal-background"></div>

    <div style="margin-top: 40px" class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">{{ __('Assign tagged vlans to this port') }}</p>
        </header>
        <section class="modal-card-body">
            <label class="label is-small">{{ __('Selected port') }}</label>
            <input type="text" wire:model="name" class="input is-small name" name="port_id" readonly="true">
            <br><br>

            <label class="label is-small">{{ __('Available vlans') }}
                <span class="is-pulled-right">
                    <a href="#" wire:click="changeNameLabel(1)">Name</a> / <a href="#" wire:click="changeNameLabel(2)">ID</a>
                </span>
            </label>

            <div id="clickable-vlans">
                @foreach ($vlans as $key => $vlan)
                    @if($nameLabel == 1)
                    <span wire:click="updateTaggedVlans({{ $vlan['id'] }})" title="{{ $vlan['vlan_id'] }} ({{ $vlan['name'] }})" class="is-clickable tag @if(isset($taggedVlans[$vlan['id']])) is-primary @endif"
                        data-id="{{ $key }}">{{ $vlan['name'] }}</span>
                    @elseif($nameLabel == 2)
                    <span wire:click="updateTaggedVlans({{ $vlan['id'] }})" title="{{ $vlan['vlan_id'] }} ({{ $vlan['name'] }})" class="is-clickable tag @if(isset($taggedVlans[$vlan['id']])) is-primary @endif"
                        data-id="{{ $key }}">{{ $vlan['vlan_id'] }}</span>
                    @endif
                @endforeach
            </div>

            <br><br>

            <p>
                <label class="label is-small">{{ __('Legend') }}</label>
                <span class="tag is-info is-small">Allowed Untagged</span>
                <span class="tag is-primary is-small">Tagged</span>
                <span class="tag is-small">{{ __('Unused') }}</span>
            </p>

            <div class="notification is-warning typ-warning is-hidden">
                Dieser Port ist zurzeit im Access-Mode.<br>
                Beim Speichern dieses Formulars wird der Port in den Trunk-Mode gesetzt.
            </div>
        </section>
        <footer class="modal-card-foot">
            @if (Auth::user()->role >= 1)
                <button wire:click="submitToPort"
                    type="button" class="button is-submit is-primary">{{ __('Update') }}</button>
            @endif
            <button data-modal="set-tagged-vlans" type="button"
                class="is-cancel button">{{ __('Cancel') }}</button>
            <span class="is-info is-hidden">{{ __('Msg.SubmitWait') }}</span>
        </footer>
    </div>
</div>
