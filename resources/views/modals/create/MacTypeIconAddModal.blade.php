<div class="modal modal-edit-icon">
    <form action="/clients/type/icon" method="post">
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 50px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('MacFilter.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <input type="hidden" class="type" name="mac_type">
                    <label class="label">MAC Typ</label>
                    <p class="control has-icons-left">
                        <input class="input type" required readonly="true">
                        <span class="icon is-small is-left">
                            <i class="fa fa-a"></i>
                        </span>
                    </p>
                </div>
                <div class="field">
                    <input type="hidden" class="type" name="id">
                    <label class="label">Icon (Font Awesome)</label>
                    <p class="control has-icons-left">
                        <input class="input mac_icon" name="mac_icon" required placeholder="fa-" value="fa-">
                        <span class="icon is-small is-left">
                            <i class="fa fa-a"></i>
                        </span>
                    </p>
                </div>
                <a href="https://fontawesome.com/icons?d=gallery" target="_blank">Zu den Font Awesome Icons</a>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success">{{ __('Button.Save') }}</button>
                <button data-modal="edit-icon" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
