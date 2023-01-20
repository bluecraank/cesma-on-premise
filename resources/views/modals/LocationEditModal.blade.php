<div class="modal modal-edit-building">
    <form action="/building/update" method="post">
        @csrf
        @method('PUT')
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Gebäude bearbeiten</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Name</label>
                    <div class="control">
                        <input class="building-id" name="id" type="hidden" value="">
                        <input class="input building-name" name="name" type="text" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-danger">Speichern</button>
                <button onclick="$('.modal-edit-building').hide();return false;" type="button"
                    class="button">Abbrechen</button>
            </footer>
        </div>
    </form>
</div>