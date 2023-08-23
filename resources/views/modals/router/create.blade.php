<div class="modal modal-create-gateway">
    <form action="{{ route('create-gateway') }}" method="post">
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Create gateway') }}</p>
            </header>
            <section class="modal-card-body">
                @csrf
                <div class="field is-fullwidth">
                    <label class="label">{{ __('Hostname/IP') }}</label>
                    <input class="input" required type="text" name="ip" placeholder="Hostname/IP">
                </div>

                <div class="field is-fullwidth">
                    <label class="label">{{ __('Description') }}</label>
                    <input class="input" required type="text" name="desc" placeholder="{{ __('Description') }}">
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Save') }}</button>
                <button data-modal="create-gateway" type="button"
                    class="button">{{ __('Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
