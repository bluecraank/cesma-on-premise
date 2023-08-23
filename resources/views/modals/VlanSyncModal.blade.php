<div class="modal modal-sync-vlans">
    <form action="/devices/action/sync-vlans" id="form-sync-vlans" method="post">
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Switch.Sync.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="content">
                    <p>
                        {!! __('Switch.Sync.Text') !!}
                        <br>
                    </p>

                    <div class="field">
                        <label class="label">Standort</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="site_id" readonly>
                                    <option value="{{ Auth::user()->currentSite()->id }}">
                                        {{ Auth::user()->currentSite()->name }}</option>
                                </select>
                            </div>
                        </div>

                        <br>

                        <label class="label">{{ __('Options') }}</label>
                        <div class="field">
                            <label class="checkbox">
                                <input type="checkbox" name="overwrite-vlan-name">
                                {{ __('Switch.Sync.OverwriteName') }}
                                <span class="help">Nur Aruba-Switche</span>
                            </label>
                        </div>

                        <div class="field">
                            <label class="checkbox">
                                <input type="checkbox" name="tag-vlan-to-uplink">
                                {{ __('Switch.Sync.TaggedVlans') }}
                            </label>
                            <span class="help">Nur ArubaOS</span>
                        </div>

                        <div class="field">
                            <label class="checkbox">
                                <input type="checkbox" name="create-if-not-exists">
                                {{ __('Switch.Sync.CreateVlans') }}
                                <span class="help">Nur Aruba-Switche</span>
                            </label>
                        </div>

                        <div class="field">
                            <label class="checkbox">
                                <input type="checkbox" name="test-mode" checked>
                                {{ __('Switch.Sync.TestMode') }}
                            </label>
                        </div>

                        <input type="hidden" value="on" name="show-results" checked>

                    </div>
            </section>
            <footer class="modal-card-foot">
                <button data-prevent="false" class="button submit is-primary"
                    onclick="$('.sync-vlan-cancel').addClass('is-hidden');">{{ __('Button.Sync') }}</button>
                <button data-modal="sync-vlans" type="button"
                    class="button sync-vlan-cancel">{{ __('Cancel') }}</button>

                <span class="sync-vlan-info help is-size-6 is-hidden">{{ __('Msg.SubmitWait') }}</span>
            </footer>
        </div>
    </form>
</div>
