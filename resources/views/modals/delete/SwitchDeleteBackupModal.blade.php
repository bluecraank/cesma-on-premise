<div class="modal modal-delete-backup">
    <form action="/switch/backup/delete" method="post">
        <input type="hidden" name="_method" value="delete" />
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Delete.Title') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Backup-ID</label>
                    <div class="control">
                        <input class="backup-id input" name="id" type="text" readonly="true" value="">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Backup.Created') }}</label>
                    <div class="control">
                        <input class="input backup-date" type="text" disabled value="">
                        <input class="input backup-date" name="date" type="hidden" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-danger">{{ __('Button.Delete') }}</button>
                <button onclick="$('.modal-delete-backup').hide();return false;" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>