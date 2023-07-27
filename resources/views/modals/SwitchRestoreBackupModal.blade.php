<div class="modal modal-upload-backup">
    <form action="/device/backup/restore" method="post">
        <input class="id input" name="id" type="hidden" value="">
        <input class="device_id input" name="device-id" type="hidden" value="">

        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Backup.Restore.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Switch</label>
                    <div class="control">
                        <input class="name input device_name" required type="text" name="switch" disabled value="">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Backup.Created') }}</label>
                    <div class="control">
                        <input class="input date" required type="text" name="date" disabled value="">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Backup.Restore.SwitchPassword') }}</label>
                    <div class="control">
                        <input class="input" type="password" name="password-switch" required placeholder="Dein Passwort">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-warning">{{ __('Backup.Restore') }}</button>
                <button data-modal="upload-backup" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>