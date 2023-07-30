<div class="modal modal-delete-backup">
    <form action="{{ route('delete-backup') }}" method="post">
        <input type="hidden" name="_method" value="delete" />
        @csrf
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Delete.Backup') }}</p>
            </header>
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Backup-ID</label>
                    <div class="control">
                        <input class="id input" name="id" type="text" readonly="true" value="">
                    </div>
                </div>
                <div class="field">
                    <label class="label">{{ __('Backup.Created') }}</label>
                    <div class="control">
                        <input class="input date" type="text" disabled value="">
                        <input class="input date" name="date" type="hidden" value="">
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button submit is-danger">{{ __('Button.Delete') }}</button>
                <button data-modal="delete-backup" type="button"
                    class="button">{{ __('Button.Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>