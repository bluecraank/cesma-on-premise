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
                        <input required class="input desc" disabled name="name" value="" type="text" placeholder="Name">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-warning">{{ __('Button.Delete') }}</button>
                <button onclick="$('.modal-delete-key').hide();return false;" type="button" class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>