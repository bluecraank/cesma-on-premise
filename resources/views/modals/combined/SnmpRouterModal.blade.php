<div class="modal modal-add-router">
    <form onsubmit="$('.modal-card-foot .submit').addClass('is-loading')" action="/router" method="post">
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Vorlage erstellen</p>
            </header>
            <section class="modal-card-body">
                @csrf
                <div class="field is-fullwidth">
                    <label class="label">Hostname/IP</label>
                    <input class="input" required type="text" name="ip" placeholder="Hostname/IP">
                </div>

                <div class="field is-fullwidth">
                    <label class="label">Beschreibung</label>
                    <input class="input" required type="text" name="desc" placeholder="Beschreibung">
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-add-router').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>

<div class="modal modal-edit-router">
    <form onsubmit="$('.modal-card-foot .submit').addClass('is-loading')" action="/router" method="post">
        @method('PUT')
        <input class="input id" required type="hidden" name="id">
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Router bearbeiten</p>
            </header>
            <section class="modal-card-body">
                @csrf
                <div class="field is-fullwidth">
                    <label class="label">Hostname/IP</label>
                    <input class="input ip" required type="text" name="ip" placeholder="Hostname/IP">
                </div>

                <div class="field is-fullwidth">
                    <label class="label">Beschreibung</label>
                    <input class="input desc" required type="text" name="desc" placeholder="Beschreibung">
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Button.Save') }}</button>
                <button onclick="$('.modal-edit-router').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>

<div class="modal modal-delete-router">
    <form onsubmit="$('.modal-card-foot .submit').addClass('is-loading')" action="/router" method="post">
        @method('DELETE')
        <input class="input id" required type="hidden" name="id">
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Router löschen</p>
            </header>
            <section class="modal-card-body">
                @csrf
                <div class="field is-fullwidth">
                    <label class="label">Hostname/IP</label>
                    <input class="input ip" required type="text" name="ip" placeholder="Hostname/IP">
                </div>

                <div class="field is-fullwidth">
                    <label class="label">Beschreibung</label>
                    <input class="input desc" required type="text" name="desc" placeholder="Beschreibung">
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-danger">{{ __('Button.Delete') }}</button>
                <button onclick="$('.modal-delete-router').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
