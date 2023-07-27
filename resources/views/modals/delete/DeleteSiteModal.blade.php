<div class="modal modal-delete-site">
    <form action="{{  route('delete-site') }}" method="post">
        @csrf
        @method('DELETE')
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Delete.Location') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">{{ __('Delete.Location.Desc') }}</label>
                    <div class="control">
                        <input class="id" name="id" type="hidden" value="">
                        <input class="name" name="name" type="hidden" value="">
                        <input class="input name" disabled type="text" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-danger">{{ __('Button.Delete') }}
                </button>
                <button data-modal="delete-site" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>