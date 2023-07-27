<div class="modal modal-sync-pubkeys">
    <form action="/device/action/sync-pubkeys" onsubmit="event.preventDefault(); switchSyncPubkeys();" id="form-sync-pubkeys"
        method="post">
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Switch.Pubkey.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">{{ __('Switch.Pubkey.FollowingKeys') }}</label>
                    <div class="control">
                        <ul class="ml-5" style="list-style-type:circle">
                            @empty($keys_list)
                                <li>{{ __('Switch.Pubkey.NoKeys') }}</li>
                            @endempty
                            
                            @foreach ($keys_list as $key)
                                <li>{{ $key }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button @disabled(empty($keys_list)) class="button is-primary">{{ __('Button.Sync') }}</button>
                <button onclick="$('.modal-sync-pubkeys').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
