<div class="modal modal-delete-vlan">
    <form action="{{ route('vlans') }}" method="post">
        @csrf
        @method('DELETE')

        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">VLAN löschen</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <input type="hidden" name="vid" class="id" value="">
                    <label class="label">Soll das VLAN wirklich gelöscht werden?</label>
                    <p class="control has-icons-left">
                        <input class="input name" type="text" name="name" readonly="true">
                        <span class="icon is-small is-left">
                            <i class="fa fa-a"></i>
                        </span>
                    </p>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-danger">{{ __('Button.Delete') }}</button>
                <button data-modal="delete-vlan" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
