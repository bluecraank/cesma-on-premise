<div class="modal modal-delete-mac">
    <form action="/clients/type" method="post">
        <input type="hidden" value="" name="id" class="id">
        @csrf
        @method('DELETE')
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('MacFilter.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">MAC Prefix*</label>
                    <p class="control has-icons-left">
                        <input class="input prefix" name="mac_prefix" readonly="true" required placeholder="MAC Address oder Prefix">
                        <span class="icon is-small is-left">
                            <i class="fa fa-a"></i>
                        </span>
                    </p>
                </div>

                <div class="field">
                    <label class="label">{{ __('Typ') }}</label>
                    <p class="control has-icons-left">
                        <input class="input type" name="mac_type" readonly="true" placeholder="MAC Typ (neu)">
                        <span class="icon is-small is-left">
                            <i class="fa fa-info"></i>
                        </span>
                    </p>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-danger">{{ __('Button.Delete') }}</button>
                <button data-modal="delete-mac" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
