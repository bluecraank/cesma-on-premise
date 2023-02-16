<div class="modal modal-add-template">
    <form onsubmit="$('.modal-card-foot .submit').addClass('is-loading')" action="/vlan-template" method="post">

        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Vorlage erstellen</p>
            </header>
            <section class="modal-card-body">
                @csrf
                <div class="field is-fullwidth">
                    <label class="label">Vorlagenname</label>
                    <input class="input" required type="text" name="name" placeholder="Vorlagenname">
                </div>

                <label class="label">VLANs</label>
                <div class="field">
                    <p class="control is-expanded">
                    <div>
                        <select multiple="multiple" id="vlan-select-ms" name="vlans_selected[]">
                            @foreach ($vlans as $vlan)
                                <option value="{{ $vlan->id }}">{{ $vlan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    </p>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-add-template').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>

<div class="modal modal-edit-template">
    <form onsubmit="$('.modal-card-foot .submit').addClass('is-loading')" action="/vlan-template" method="post">

        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Vorlage bearbeiten</p>
            </header>
            <section class="modal-card-body">
                @csrf
                @method('PUT')
                <input class="input id" required type="hidden" name="id">
                <div class="field is-fullwidth">
                    <label class="label">Vorlagenname</label>
                    <input class="input name" required type="text" name="name" placeholder="Vorlagenname">
                </div>

                <label class="label">VLANs</label>
                <div class="field">
                    <p class="control is-expanded">
                    <div>
                        <select class="vlans" multiple="multiple" id="vlan-select-ms-2" name="vlans_selected[]">
                            @foreach ($vlans as $vlan)
                                <option data-id="{{ $vlan->id }}" value="{{ $vlan->id }}">{{ $vlan->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    </p>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-edit-template').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>

<div class="modal modal-delete-template">
    <form onsubmit="$('.modal-card-foot .submit').addClass('is-loading')" action="/vlan-template" method="post">

        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Vorlage löschen</p>
            </header>
            <section class="modal-card-body">
                @csrf
                @method('DELETE')
                <input class="input id" required type="hidden" name="id">

                <div class="field is-fullwidth">
                    <label class="label">Vorlagenname</label>
                    <input class="input name" required type="text" name="name" placeholder="Vorlagenname"
                        readonly="true">
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-danger">{{ __('Button.Delete') }}</button>
                <button onclick="$('.modal-delete-template').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
