<div class="modal modal-delete-key">
    <form action="/pubkey/delete" method="post">
        @csrf
        @method('DELETE')
        <input type="hidden" value="" class="id" name="id">
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Modal.Key.Delete') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">{{ __('Modal.Key.Delete.Desc') }}</label>
                    <div class="control">
                        <input required class="input description" readonly="true" name="name" value="" type="text" placeholder="Description">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-danger">{{ __('Button.Delete') }}</button>
                <button data-modal="delete-key" type="button" class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>