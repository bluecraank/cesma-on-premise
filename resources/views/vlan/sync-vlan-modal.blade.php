<div class="modal modal-sync-vlans">
    <form action="/switch/every/vlans" id="form-sync-vlans" method="post">
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

                    <label class="label">{{ __('Options') }}</label>
                    <div class="field">
                        <label class="checkbox">
                            <input type="checkbox" name="create-if-not-exists">
                            {{ __('Switch.Sync.CreateVlans') }}
                        </label>
                    </div>

                    <div class="field">
                        <label class="checkbox">
                            <input type="checkbox" name="test-mode">
                            {{ __('Switch.Sync.TestMode') }}
                        </label>
                    </div>

                    <div class="field">
                        <label class="checkbox">
                            <input type="checkbox" name="show-results" checked>
                            {{ __('Switch.Sync.ShowResults') }}
                        </label>
                    </div>
                 </div> 
            </section>
            <footer class="modal-card-foot">
                <button class="button is-primary sync-vlan-start" onclick="$(this).addClass('is-loading');$('.sync-vlan-info').removeClass('is-hidden');$('.sync-vlan-cancel').addClass('is-hidden');">{{ __('Button.Sync') }}</button>
                <button onclick="$('.modal-sync-vlans').hide();return false;" type="button"
                    class="button sync-vlan-cancel">{{ __('Button.Cancel') }}</button>

                <span class="sync-vlan-info help is-size-6 is-hidden">{{ __('Switch.Sync.Wait') }}</span>
            </footer>
        </div>
    </form>
</div>