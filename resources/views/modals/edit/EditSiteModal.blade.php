<div class="modal modal-edit-site">
    <form action="{{  route('sites') }}" method="post">
        @csrf
        @method('PUT')
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Edit.Location') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Standortname</label>
                    <div class="control">
                        <input class="id" name="id" type="hidden" value="">
                        <input class="input name" name="name" type="text" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-danger">{{ __('Button.Save') }}</button>
                <button data-modal="edit-site" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>