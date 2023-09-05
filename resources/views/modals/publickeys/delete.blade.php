<div class="modal modal-delete-public-key">
    <form action="{{ route('delete-public-key') }}" method="post">
        @csrf
        @method('DELETE')
        <input type="hidden" value="" class="id" name="id">
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Delete ssh public key') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">{{ __('User') }}</label>
                    <div class="control">
                        <input required class="input description" readonly="true" name="name" value="" type="text" placeholder="Description">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-danger">{{ __('Delete') }}</button>
                <button data-modal="delete-public-key" type="button" class="button">{{ __('Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
