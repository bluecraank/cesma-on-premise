<div class="modal modal-delete-gateway">
    <form action="{{ route('delete-gateway') }}" method="post">
        @method('DELETE')
        <input class="input id" required type="hidden" name="id">
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Delete gateway') }}</p>
            </header>
            <section class="modal-card-body">
                @csrf
                <div class="field is-fullwidth">
                    <label class="label">Hostname/IP</label>
                    <input class="input ip" required  readonly="true" type="text" name="ip" placeholder="Hostname/IP">
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-danger">{{ __('Delete') }}</button>
                <button data-modal="delete-gateway" type="button"
                    class="button">{{ __('Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
