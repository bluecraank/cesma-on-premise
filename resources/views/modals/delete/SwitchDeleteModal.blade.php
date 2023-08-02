<div class="modal modal-delete-switch">
    <form onsubmit="$('.modal-delete-switch .is-submit').addClass('is-loading')" action="{{ route('delete-switch') }}" method="post">
        <input type="hidden" name="_method" value="delete" />
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Switch.Delete.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">{{ __('Switch.Delete.Desc') }}</label>
                    <div class="control">
                        <input class="id" name="id" type="hidden" value="">
                        <input class="name" name="name" type="hidden" value="">
                        <input class="input name" disabled type="text" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button no-prevent is-submit is-danger">{{ __('Button.Delete') }}</button>
                <button data-modal="delete-switch" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>