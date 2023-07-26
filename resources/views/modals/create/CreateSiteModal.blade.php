<div class="modal modal-add-site">
    <form onsubmit="$('.modal-card-foot .submit').addClass('is-loading')" action="{{  route('create-site') }}" method="post">
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Standort erstellen</p>
            </header>
            <section class="modal-card-body">
                @csrf
                <div class="field">
                    <label for="" class="label">Standortname</label>
                    <div class="control is-expanded">
                        <input required class="input" name="name" type="text" placeholder="Stadt / Ort">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Button.Save') }}</button>
                <button data-modal="add-site" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
