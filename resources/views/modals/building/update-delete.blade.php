<div>
    <div class="modal modal-update-building" @if ($show && $modal == 'update') style="display:block" @endif>
        <form wire:submit="update">
            <div class="modal-background"></div>
            <div style="margin-top: 40px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Edit building') }}</p>
                </header>
                <section class="modal-card-body">
                    @if (!$show)
                        <div class="loader-wrapper is-active">
                            <div class="loader is-loading"></div>
                        </div>
                    @else
                        <div class="field">
                            <label class="label">Name</label>
                            <div class="control">
                                <input wire:model="name" class="input name @error('name') is-danger @enderror"
                                    name="name" type="text">
                                @error('name')
                                    <span class="help is-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    @endif
                </section>
                <footer class="modal-card-foot">
                    <button type="submit" class="button no-prevent is-success">{{ __('Save') }}</button>
                    <button wire:click="close()" type="button" class="button">{{ __('Cancel') }}</button>
                </footer>
            </div>
        </form>
    </div>

    <div class="modal modal-delete-building" @if ($show && $modal == 'delete') style="display:block" @endif>
        <form wire:submit="delete">
            <div class="modal-background"></div>
            <div style="margin-top: 40px" class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">{{ __('Delete building') }}</p>
                </header>
                <section class="modal-card-body">
                    @if (!$show)
                        <div class="loader-wrapper is-active">
                            <div class="loader is-loading"></div>
                        </div>
                    @else
                        <div class="field">
                            <label class="label">{{ __('Are you sure to delete this building?') }}</label>
                            <div class="control">
                                <input class="input name" disabled type="text" value="{{ $building->name }}">
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
</div>
