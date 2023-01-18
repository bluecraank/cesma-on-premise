<div class="modal modal-new-key">
    <form action="/pubkey/add" method="post">
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Modal.Key.Add') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">{{ __('Modal.Key.Add.Name') }}</label>
                    <div class="control">
                        <input required class="input" name="description" type="text" placeholder="Name this Key">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Modal.Key.Add.Pubkey') }}</label>
                    <div class="control">
                        <input required class="input" name="key" type="text" placeholder="ssh-rsa AAACHDK...">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success">{{ __('Button.Add') }}</button>
                <button onclick="$('.modal-new-key').hide();return false;" type="button" class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>