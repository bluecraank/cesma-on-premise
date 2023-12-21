<div class="modal modal-create-vlan">
    <form action="{{ route('vlans') }}" method="post">
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Vlan.Add') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <input type="hidden" class="vlan-id" name="id">
                    <label class="label">Name</label
                    <p class="control">
                        <input class="input vlan-name" name="name" placeholder="Name des VLANs">
                    </p>
                </div>
                <div class="field">
                    <label class="label">ID</label>
                    <p class="control">
                        <input class="input vlan-vid" type="number" max="4096" min="1" name="vid" placeholder="VLAN ID">
                    </p>
                </div>
                <div class="field">
                    <label class="label">{{ __('Description') }}</label>
                    <p class="control">
                        <input class="input vlan-desc" name="description" placeholder="VLAN Beschreibung">
                    </p>
                </div>

                <div class="field">
                    <label class="label">{{ __('Site') }}</label>
                    <div class="select is-fullwidth">
                        <select name="site_id" readonly>
                            <option value="{{ Auth::user()->currentSite()->id }}">{{ Auth::user()->currentSite()->name }}</option>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Network range') }}</label>
                    <p class="control">
                        <input class="input vlan-ip_range" name="ip_range" placeholder="10.10.10.0/24">
                    </p>
                </div>
                <div class="field">
                    <label class="checkbox">
                        <input type="checkbox" name="sync" class="vlan-sync">
                        {{ __('Enable to sync this vlan') }}
                    </label>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success no-prevent">{{ __('Save') }}</button>
                <button data-modal="create-vlan" type="button"
                    class="button">{{ __('Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
