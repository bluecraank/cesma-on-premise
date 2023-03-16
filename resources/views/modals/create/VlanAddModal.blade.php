<div class="modal modal-add-vlan">
    <form action="/vlan" method="post">
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Vlan.Add') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <input type="hidden" class="vlan-id" name="id">
                    <label class="label">Name*</label>
                    <p class="control has-icons-left">
                        <input class="input vlan-name" name="name" placeholder="Name des VLANs">
                        <span class="icon is-small is-left">
                            <i class="fa fa-a"></i>
                        </span>
                    </p>
                </div>
                <div class="field">
                    <label class="label">ID</label>
                    <p class="control has-icons-left">
                        <input class="input vlan-vid" name="vid" placeholder="VLAN Beschreibung">
                        <span class="icon is-small is-left">
                            <i class="fa fa-info"></i>
                        </span>
                    </p>
                </div>
                <div class="field">
                    <label class="label">{{ __('Description') }}</label>
                    <p class="control has-icons-left">
                        <input class="input vlan-desc" name="description" placeholder="VLAN Beschreibung">
                        <span class="icon is-small is-left">
                            <i class="fa fa-info"></i>
                        </span>
                    </p>
                </div>

                <div class="field">
                    <label class="label">{{ __('Location') }}</label>
                    <div class="select is-fullwidth">
                        <select name="location_id">
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Vlan.Subnet') }}</label>
                    <p class="control has-icons-left">
                        <input class="input vlan-ip_range" name="ip_range" placeholder="10.10.10.0/24">
                        <span class="icon is-small is-left">
                            <i class="fa fa-up-down"></i>
                        </span>
                    </p>
                </div>
                <div class="field">
                    <label class="checkbox">
                        <input type="checkbox" name="scan" class="vlan-scan">
                        {{ __('Vlan.Scan') }}
                    </label>
                </div>
                <div class="field">
                    <label class="checkbox">
                        <input type="checkbox" name="sync" class="vlan-sync">
                        {{ __('Vlan.Sync') }}
                    </label>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-add-vlan').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
