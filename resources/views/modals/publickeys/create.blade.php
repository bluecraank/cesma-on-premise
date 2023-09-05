<div class="modal modal-create-public-key">
    <form action="{{ route('create-public-key') }}" method="post">
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Add ssh public key') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">{{ __('User') }}</label>
                    <div class="control">
                        <input required class="input" name="description" type="text" placeholder="Name this Key">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('SSH public key') }}</label>
                    <div class="control">
                        <input required class="input" name="key" type="text" placeholder="ssh-rsa AAACHDK...">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Create') }}</button>
                <button data-modal="create-public-key" type="button" class="button">{{ __('Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
