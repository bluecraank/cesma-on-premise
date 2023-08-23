<div class="modal modal-delete-room" style="display:block">
    <form wire:submit="delete">
        <div class="modal-background"></div>
        <div style="margin-top: 40px" class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ __('Delete room') }}</p>
            </header>
            <section class="modal-card-body">
                @if (!$showTaggedModal)
                    <div class="loader-wrapper is-active">
                        <div class="loader is-loading"></div>
                    </div>
                @else
                    <div class="field">
                        <label class="label">{{ __('Are you sure to delete this room?') }}</label>
                        <div class="control">
                            <input class="input name" disabled type="text">
                        </div>
                    </div>
                @endif
            </section>
            <footer class="modal-card-foot">
                <button type="submit" class="button no-prevent is-danger">{{ __('Delete') }}</button>
                <button wire:click="close()" type="button" class="button">{{ __('Cancel') }}</button>
            </footer>
        </div>
    </form>
</div>
