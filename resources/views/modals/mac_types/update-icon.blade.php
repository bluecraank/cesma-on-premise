<div class="modal modal-update-mac-type-icon">
    <form action="{{ route('update-mac-type-icon') }}" method="post">
        @csrf
        @method('PUT')
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('MacFilter.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <input type="hidden" class="mac_type" name="mac_type">
                    <label class="label">MAC Typ</label>
                    <p class="control has-icons-left">
                        <input class="input mac_type" required readonly="true">
                        <span class="icon is-small is-left">
                            <i class="mdi mdi-ethernet"></i>
                        </span>
                    </p>
                </div>
                <div class="field">
                    <input type="hidden" class="type" name="id">
                    <label class="label">Icon (MDI Icons)</label>
                    <p class="control has-icons-left">
                        <input class="input mac_icon" name="mac_icon" required placeholder="mdi-information" value="mdi-">
                        <span class="icon is-small is-left">
                            <i class="mdi mdi-information"></i>
                        </span>
                    </p>
                </div>
                <a href="https://pictogrammers.com/library/mdi/" target="_blank">{{ __('Click here to open mdi icon library') }}</a>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-success">{{ __('Save') }}</button>
                <button data-modal="update-mac-type-icon" type="button"
                    class="button">{{ __('Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
