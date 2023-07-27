<div class="modal modal-edit-location">
    <form onsubmit="$('.submit').addClass('is-loading')" action="{{  route('sites') }}" method="post">
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
                        <input class="location-id" name="id" type="hidden" value="">
                        <input class="input location-name" name="name" type="text" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-danger">{{ __('Button.Save') }}</button>
                <button data-modal="edit-location" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>