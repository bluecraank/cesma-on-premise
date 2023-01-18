<div class="modal modal-delete-vlan">
    <form action="/vlan/delete" method="post">
        @csrf
        <input type="hidden" value="DELETE" name="_method">
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">VLAN löschen</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <input type="hidden" name="id" class="vlan-id" value="">
                    <label class="label">Möchtest du das VLAN wirklich löschen?</label>
                    <p class="control has-icons-left">
                        <input class="input vlan-name" type="text" name="name" readonly="true">
                        <span class="icon is-small is-left">
                            <i class="fa fa-a"></i>
                        </span>
                    </p>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success">Löschen</button>
                <button onclick="$('.modal-delete-vlan').hide();return false;" type="button"
                    class="button">Abbrechen</button>
            </footer>
        </div>
    </form>
</div>
