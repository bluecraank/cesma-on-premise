<div class="modal modal-sync-pubkeys">
    <form id="form-sync-pubkeys" method="post">
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Switch.Pubkey.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <p class="mb-3">Hier können die hinterlegten öffentlichen SSH-Schlüssel mit Aruba-Switchen synchronisiert werden.</p>
                <div class="field mr-2">
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

                <div class="notification-wrapper"></div>
            </section>
            <footer class="modal-card-foot">
                <button id="actionSyncPubkeys" @disabled(empty($keys_list))
                    class="button submit is-primary">{{ __('Button.Sync') }}</button>
                <button data-modal="sync-pubkeys" type="button" class="button">{{ __('Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
