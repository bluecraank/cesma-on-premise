<div class="modal modal-delete-backup" @if($show) style="display:block" @endif>
    <form wire:submit="delete" method="post">
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Delete backup') }}</p>
            </header>
            <section class="modal-card-body">
                @if (!$show)
                    <div class="loader-wrapper is-active">
                        <div class="loader is-loading"></div>
                    </div>
                @else
                    <div class="field">
                        <label class="label">Backup-ID</label>
                        <div class="control">
                            <input wire:model="id" class="id input" name="id" type="text" readonly="true"
                                value="">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">{{ __('Created') }}</label>
                        <div class="control">
                            <input wire:model="created_at" class="input date" name="date" type="text" readonly="true">
                        </div>
                    </div>
                @endif
            </section>
            <footer class="modal-card-foot">
                <button type="submit" class="button no-prevent submit is-danger">{{ __('Delete') }}</button>
                <button wire:click="close()" type="button" class="button">{{ __('Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
