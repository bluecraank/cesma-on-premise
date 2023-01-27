<div class="modal modal-add-site">
    <form onsubmit="$('.modal-card-foot .submit').addClass('is-loading')" action="/location" method="post">
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
                        <input class="input" name="name" type="text" placeholder="Stadt / Ort">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-add-site').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
